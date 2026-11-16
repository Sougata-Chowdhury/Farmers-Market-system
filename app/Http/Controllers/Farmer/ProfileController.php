<?php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use App\Http\Requests\BankDetailsRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('farmer.profile', [
            'user' => $request->user(),
            'profile' => $request->user()->farmerProfile,
        ]);
    }

    public function update(BankDetailsRequest $request): RedirectResponse
    {
        $request->user()->farmerProfile()->updateOrCreate([], $request->validated());

        return back()->with('status', 'Payout details saved.');
    }
}
