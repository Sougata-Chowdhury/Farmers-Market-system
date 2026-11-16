<?php

namespace Tests\Feature;

use App\Mail\LowStockDigest;
use App\Mail\OrderPlaced;
use App\Mail\OrderPlacedForFarmer;
use App\Mail\OrderStatusChanged;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Notifications\LowStockNotification;
use App\Notifications\NewSaleNotification;
use App\Notifications\OrderPlacedNotification;
use App\Notifications\OrderStatusChangedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class OrderNotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    private User $farmer;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        Notification::fake();

        $this->customer = User::factory()->create([
            'phone' => '01712345678',
            'address' => '12 Green Road, Dhaka',
        ]);

        $this->farmer = User::factory()->farmer()->create();
        $this->product = $this->product($this->farmer);
    }

    public function test_placing_an_order_emails_the_customer_and_the_farmer(): void
    {
        $this->placeOrder();

        Mail::assertQueued(OrderPlaced::class, fn (OrderPlaced $mail) => $mail->hasTo($this->customer->email));
        Mail::assertQueued(OrderPlacedForFarmer::class, fn (OrderPlacedForFarmer $mail) => $mail->hasTo($this->farmer->email));

        Notification::assertSentTo($this->customer, OrderPlacedNotification::class);
        Notification::assertSentTo($this->farmer, NewSaleNotification::class);
    }

    public function test_status_changes_email_and_notify_the_customer(): void
    {
        $this->placeOrder();
        $order = Order::firstOrFail();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->patch(route('admin.orders.status', $order), ['status' => 'confirmed'])
            ->assertSessionHasNoErrors();

        Mail::assertQueued(OrderStatusChanged::class, fn (OrderStatusChanged $mail) => $mail->hasTo($this->customer->email));
        Notification::assertSentTo($this->customer, OrderStatusChangedNotification::class);
    }

    public function test_cancelling_an_order_notifies_the_customer(): void
    {
        $this->placeOrder();
        $order = Order::firstOrFail();

        $this->actingAs($this->customer)
            ->post(route('orders.cancel', $order))
            ->assertSessionHasNoErrors();

        Mail::assertQueued(OrderStatusChanged::class);
        Notification::assertSentTo($this->customer, OrderStatusChangedNotification::class);
    }

    public function test_low_stock_digest_command_emails_farmers(): void
    {
        $this->product->update(['stock' => 3]);

        $this->artisan('marketplace:low-stock-digest')->assertSuccessful();

        Mail::assertQueued(LowStockDigest::class, fn (LowStockDigest $mail) => $mail->hasTo($this->farmer->email));
        Notification::assertSentTo($this->farmer, LowStockNotification::class);
    }

    public function test_customers_can_mark_a_notification_as_read(): void
    {
        $notification = $this->storeNotification($this->customer);

        $this->actingAs($this->customer)
            ->post(route('notifications.read', $notification->id))
            ->assertRedirect();

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_customers_can_mark_all_notifications_as_read(): void
    {
        $this->storeNotification($this->customer);
        $this->storeNotification($this->customer);

        $this->actingAs($this->customer)
            ->post(route('notifications.read-all'))
            ->assertRedirect();

        $this->assertSame(0, $this->customer->unreadNotifications()->count());
    }

    public function test_users_cannot_open_another_users_notification(): void
    {
        $notification = $this->storeNotification($this->customer);

        $other = User::factory()->create();

        $this->actingAs($other)
            ->post(route('notifications.read', $notification->id))
            ->assertNotFound();
    }

    /**
     * Bypass the Notification fake so the row is really written to the database.
     */
    private function storeNotification(User $user): DatabaseNotification
    {
        return $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => OrderPlacedNotification::class,
            'data' => [
                'title' => 'Order placed',
                'body' => '2 items',
                'url' => route('orders.index'),
                'kind' => 'order',
            ],
        ]);
    }

    private function placeOrder(): Order
    {
        $this->actingAs($this->customer)->post(route('cart.store', $this->product), ['quantity' => 2]);
        $this->actingAs($this->customer)->post(route('checkout.store'), [
            'name' => 'Test Customer',
            'phone' => '01712345678',
            'address' => '12 Green Road, Dhaka',
        ]);

        return Order::latest('id')->firstOrFail();
    }

    private function product(User $farmer): Product
    {
        $category = Category::firstOrCreate(
            ['name' => 'Vegetables'],
            ['slug' => 'vegetables'],
        );

        return Product::create([
            'farmer_id' => $farmer->id,
            'category_id' => $category->id,
            'name' => 'Fresh Tomato',
            'description' => 'Farm fresh tomatoes.',
            'price' => 100,
            'stock' => 10,
            'unit' => 'kg',
            'is_active' => true,
        ]);
    }
}
