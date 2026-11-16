<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(): View
    {
        $customers = User::query()
            ->where('role', UserRole::Customer)
            ->withCount('orders')
            ->withSum('orders as lifetime_value', 'total')
            ->orderByDesc('lifetime_value')
            ->paginate(15);

        return view('admin.customers.index', compact('customers'));
    }
}
