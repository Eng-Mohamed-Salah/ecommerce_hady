<?php

namespace App\Http\Controllers\Getway\Paypal;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Srmklive\PayPal\Services\ExpressCheckout;
class PaypalController extends Controller
{
    // Payment
    public function payment()
    {
        // Get Cart Items With Products
        $cartItems = Cart::where('user_id', auth()->id())->with('product')->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['error' => 'Cart is empty'], 400);
        }

        $data = [];
        $totalAmount = 0;

        foreach ($cartItems as $item) {
            $data['items'][] = [
                'name'  => $item->product->title,
                'price' => $item->product->price,
                'desc'  => $item->product->description,
                'qty'   => $item->count
            ];
            $totalAmount += $item->product->price * $item->count;
        }

        $data['invoice_id'] = uniqid();
        $data['invoice_description'] = "Order #{$data['invoice_id']} Invoice";
        $data['return_url'] = env('APP_URL').'/api/paypal/payment/success';
        $data['cancel_url'] = env('APP_URL').'/api/paypal/payment/cancel';
        $data['total'] = $totalAmount;

        $provider = new ExpressCheckout;
        $response = $provider->setExpressCheckout($data);

        if (isset($response['paypal_link'])) {
            return redirect()->away($response['paypal_link']);
        }

        // Return Error Payment
        return response()->json(['error' => 'Unable to create PayPal payment link.'], 500);
    }
    // Success
    public function success(Request $request)
    {
        $provider = new ExpressCheckout;
        $response = $provider->getExpressCheckoutDetails($request->token);

        if(in_array(strtoupper($response['ACK']), ['SUCCESS', 'SUCCESSWITHWARNING'])) {
            $cartItems = Cart::where('user_id', auth()->id())->with('product')->get();

            foreach ($cartItems as $item) {
                Transaction::create([
                    'user_id'    => auth()->id(),
                    'product_id' => $item->product_id,
                    'status'     => 'success',
                    'amount'     => $item->product->price * $item->count,
                    'quantity'   => $item->count,
                ]);
            }

            Cart::where('user_id', auth()->id())->delete();

            return response()->json(['message' => 'Payment Success.'], 200);
        }

        return response()->json(['message' => 'Fail Payment'], 402);
    }

    // Cancel Payment Method
    public function cancel()
    {
        $cartItems = Cart::where('user_id', auth()->id())->with('product')->get();

        foreach ($cartItems as $item) {
            Transaction::create([
                'user_id'    => auth()->id(),
                'product_id' => $item->product_id,
                'status'     => 'failed',
                'amount'     => $item->product->price * $item->count,
                'quantity'   => $item->count,
            ]);
        }

        return response()->json(['message' => 'Payment Canceled.'], 402);
    }

    public function userTransactions()
    {
        $userId = auth()->id();

        $successfulTransactions = Transaction::where('user_id', $userId)->where('status', 'success')->count();
        $failedTransactions = Transaction::where('user_id', $userId)->where('status', 'failed')->count();
        $totalSales = Transaction::where('user_id', $userId)->where('status', 'success')->sum('amount');

        return response()->json([
            'successful_transactions' => $successfulTransactions,
            'failed_transactions' => $failedTransactions,
            'total_sales' => $totalSales,
        ]);
    }
}
