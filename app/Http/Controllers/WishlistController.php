<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index()
    {
        $wishlists = Wishlist::where('user_id', auth()->id())->take(5)->get();
        return response()->json($wishlists);
    }

    public function show($id)
    {
        $wishlistItem = Wishlist::find($id);

        if(!$wishlistItem){
            return response()->json(['message' => 'Wishlist item not found.'], 404);
        }

        $wishlists = Wishlist::where('user_id', auth()->id(),'id',$wishlistItem)->get();
        return response()->json($wishlists);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        Wishlist::create([
            'user_id' => auth()->id(),
            'product_id' => $request->product_id,
        ]);
        return response()->json(['message' => 'Product added to wishlist.']);
    }

    public function destroy($id)
    {
        $wishlistItem = Wishlist::findOrFail($id);
        $wishlistItem->delete();
        return response()->json(['message' => 'Product removed from wishlist.']);
    }

    public function moveToCart($id)
    {
        $wishlistItem = Wishlist::find($id);

        if(!$wishlistItem){
            return response()->json(['message' => 'Wishlist item not found.'], 404);
        }

        $cartItem = Cart::where('product_id', $wishlistItem->product_id)
                         ->where('user_id', auth()->id())
                         ->first();

        if ($cartItem) {
            $cartItem->increment('count');
        } else {
            Cart::create([
                'count' => 1,
                'product_id' => $wishlistItem->product_id,
                'user_id' => auth()->id(),
            ]);
        }

        $wishlistItem->delete();

        return response()->json(['message' => 'Product moved to cart and removed from wishlist.']);
    }
}
