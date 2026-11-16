<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewRequest;
use App\Models\Product;

class ReviewController extends Controller
{
    public function store(ReviewRequest $request, Product $product)
    {
        $product->reviews()->updateOrCreate(
            ['user_id' => $request->user()->id],
            $request->safe()->only(['rating', 'body'])
        );

        return back()->with('status', 'Thanks for reviewing '.$product->name.'.');
    }
}
