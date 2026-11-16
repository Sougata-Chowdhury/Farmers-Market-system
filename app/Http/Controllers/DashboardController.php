<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        return match (true) {
            $request->user()->isAdmin() => redirect()->route('admin.dashboard'),
            $request->user()->isFarmer() => redirect()->route('farmer.dashboard'),
            default => redirect()->route('orders.index'),
        };
    }
}
