<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductGalleryTest extends TestCase
{
    use RefreshDatabase;

    private User $farmer;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->farmer = User::factory()->farmer()->create();
        $this->category = Category::create(['name' => 'Vegetables', 'slug' => 'vegetables']);
    }

    public function test_farmers_can_upload_a_gallery_when_publishing(): void
    {
        $this->actingAs($this->farmer)
            ->post(route('farmer.products.store'), $this->payload([
                UploadedFile::fake()->image('tomatoes.jpg', 900, 700),
                UploadedFile::fake()->image('basket.png', 800, 600),
            ]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('farmer.products.index'));

        $product = Product::firstOrFail();

        $this->assertCount(2, $product->getMedia('gallery'));

        $first = $product->getMedia('gallery')->first();

        $this->assertTrue($first->hasGeneratedConversion('thumb'));
        $this->assertTrue($first->hasGeneratedConversion('card'));
        $this->assertTrue($first->hasGeneratedConversion('full'));
        $this->assertStringContainsString('.webp', $first->getUrl('card'));
    }

    public function test_the_cover_image_uses_the_card_conversion(): void
    {
        $product = $this->product();
        $product->addMedia(UploadedFile::fake()->image('tomatoes.jpg', 900, 700))->toMediaCollection('gallery');

        $this->assertStringContainsString('/conversions/', $product->image_url);
        $this->assertStringContainsString('-card.webp', $product->image_url);
    }

    public function test_products_without_media_fall_back_to_the_placeholder(): void
    {
        $product = $this->product();

        $this->assertStringEndsWith('images/product-placeholder.svg', $product->image_url);
        $this->assertSame($product->image_url, $product->galleryImages()[0]['full']);
    }

    public function test_the_product_page_renders_a_thumbnail_strip(): void
    {
        $product = $this->product();

        foreach (['one.jpg', 'two.jpg', 'three.jpg'] as $name) {
            $product->addMedia(UploadedFile::fake()->image($name, 900, 700))->toMediaCollection('gallery');
        }

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Product photos')
            ->assertSee('Show photo')
            ->assertSee('one-thumb.webp', false);
    }

    public function test_farmers_can_add_more_photos_to_an_existing_product(): void
    {
        $product = $this->product();
        $product->addMedia(UploadedFile::fake()->image('one.jpg'))->toMediaCollection('gallery');

        $this->actingAs($this->farmer)
            ->patch(route('farmer.products.update', $product), $this->payload([
                UploadedFile::fake()->image('two.jpg', 900, 700),
            ], updating: true))
            ->assertSessionHasNoErrors();

        $this->assertCount(2, $product->fresh()->getMedia('gallery'));
    }

    public function test_farmers_can_delete_one_of_their_photos(): void
    {
        $product = $this->product();
        $media = $product->addMedia(UploadedFile::fake()->image('one.jpg'))->toMediaCollection('gallery');

        $this->actingAs($this->farmer)
            ->delete(route('farmer.products.media.destroy', [$product, $media]))
            ->assertSessionHasNoErrors();

        $this->assertModelMissing($media);
        $this->assertCount(0, $product->fresh()->getMedia('gallery'));
    }

    public function test_farmers_cannot_delete_another_farmers_photo(): void
    {
        $product = $this->product();
        $media = $product->addMedia(UploadedFile::fake()->image('one.jpg'))->toMediaCollection('gallery');

        $intruder = User::factory()->farmer()->create();

        $this->actingAs($intruder)
            ->delete(route('farmer.products.media.destroy', [$product, $media]))
            ->assertForbidden();

        $this->assertModelExists($media);
    }

    public function test_uploads_are_validated(): void
    {
        $this->actingAs($this->farmer)
            ->post(route('farmer.products.store'), $this->payload([
                UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf'),
            ]))
            ->assertSessionHasErrors('images.0');

        $this->assertSame(0, Product::count());
    }

    private function product(): Product
    {
        return Product::create([
            'farmer_id' => $this->farmer->id,
            'category_id' => $this->category->id,
            'name' => 'Fresh Tomato',
            'description' => 'Farm fresh tomatoes.',
            'price' => 100,
            'stock' => 10,
            'unit' => 'kg',
            'is_active' => true,
        ]);
    }

    /**
     * @param  array<int, UploadedFile>  $images
     * @return array<string, mixed>
     */
    private function payload(array $images, bool $updating = false): array
    {
        return [
            'name' => $updating ? 'Fresh Tomato' : 'Vine Ripened Tomato',
            'category_id' => $this->category->id,
            'description' => 'Grown without pesticides.',
            'price' => 120,
            'stock' => 25,
            'unit' => 'kg',
            'images' => $images,
        ];
    }
}
