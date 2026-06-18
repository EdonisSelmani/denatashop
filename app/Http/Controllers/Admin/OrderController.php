<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with('user')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->latest()
            ->paginate(15);

        return view('admin.orders.index', [
            'orders' => $orders,
            'statuses' => Order::statuses(),
        ]);
    }

    public function show(Order $order)
    {
        $order->load('items.product', 'user');

        return view('admin.orders.show', [
            'order' => $order,
            'statuses' => Order::statuses(),
            'paymentStatuses' => Order::paymentStatuses(),
        ]);
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:' . implode(',', array_keys(Order::statuses()))],
            'payment_status' => ['required', 'in:' . implode(',', array_keys(Order::paymentStatuses()))],
            'tracking_number' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($order, $validated) {
            $oldStatus = $order->status;
            $newStatus = $validated['status'];

            $order->load('items.product');

            if ($oldStatus !== Order::STATUS_CANCELLED && $newStatus === Order::STATUS_CANCELLED) {
                foreach ($order->items as $item) {
                    $item->product?->increment('stock', $item->quantity);
                }
            }

            if ($oldStatus === Order::STATUS_CANCELLED && $newStatus !== Order::STATUS_CANCELLED) {
                foreach ($order->items as $item) {
                    if ($item->product && $item->product->stock < $item->quantity) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'status' => "Not enough stock to reactivate {$item->product_name}.",
                        ]);
                    }
                }

                foreach ($order->items as $item) {
                    $item->product?->decrement('stock', $item->quantity);
                }
            }

            $order->update(array_merge(
                $validated,
                $order->markStatusTimestamps($newStatus),
            ));
        });

        return redirect()->route('admin.orders.show', $order)->with('success', 'Order updated successfully.');
    }
}
