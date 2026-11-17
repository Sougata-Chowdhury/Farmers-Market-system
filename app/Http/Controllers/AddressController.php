<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddressRequest;
use App\Models\Address;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AddressController extends Controller
{
    public function index(Request $request): View
    {
        return view('addresses.index', [
            'addresses' => $request->user()
                ->addresses()
                ->orderByDesc('is_default')
                ->orderBy('id')
                ->get(),
        ]);
    }

    public function store(AddressRequest $request): RedirectResponse
    {
        $user = $request->user();
        $address = $user->addresses()->create($request->validated());

        if ($address->is_default || $user->addresses()->count() === 1) {
            $address->makeDefault();
        }

        return back()->with('status', 'Delivery address saved.');
    }

    public function setDefault(Request $request, Address $address): RedirectResponse
    {
        abort_unless($address->user_id === $request->user()->id, 403);

        $address->makeDefault();

        return back()->with('status', "{$address->label} is now your default address.");
    }

    public function destroy(Request $request, Address $address): RedirectResponse
    {
        abort_unless($address->user_id === $request->user()->id, 403);

        $wasDefault = $address->is_default;
        $address->delete();

        if ($wasDefault) {
            $request->user()->addresses()->orderBy('id')->first()?->makeDefault();
        }

        return back()->with('status', 'Delivery address removed.');
    }
}
