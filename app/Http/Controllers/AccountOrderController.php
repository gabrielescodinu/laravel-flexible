<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\SiteSetting;
use App\Support\Access\Permissions;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AccountOrderController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureEcommerceEnabled();

        $user = $request->user();
        abort_if(! $user, 403);

        $inProgressOrders = Order::query()
            ->where('user_id', $user->id)
            ->whereIn('status', [Order::STATUS_PENDING, Order::STATUS_PROCESSING])
            ->with('items')
            ->orderByDesc('id')
            ->get();

        $completedOrders = Order::query()
            ->where('user_id', $user->id)
            ->where('status', Order::STATUS_COMPLETED)
            ->with('items')
            ->orderByDesc('id')
            ->get();

        return view('account.orders.index', [
            'inProgressOrders' => $inProgressOrders,
            'completedOrders' => $completedOrders,
        ]);
    }

    public function show(Request $request, Order $order): View
    {
        $this->ensureEcommerceEnabled();

        $user = $request->user();
        abort_if(! $user, 403);

        $canViewOtherUsersOrders = $user->hasAnyPermission(Permissions::allNames());
        if (! $canViewOtherUsersOrders && (int) $order->user_id !== (int) $user->id) {
            abort(403);
        }

        $order->loadMissing(['items', 'payments']);

        return view('account.orders.show', [
            'order' => $order,
        ]);
    }

    private function ensureEcommerceEnabled(): void
    {
        if (! SiteSetting::ecommerceEnabled()) {
            abort(404);
        }
    }
}

