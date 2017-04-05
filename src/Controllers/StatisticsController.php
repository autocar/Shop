<?php

namespace Laralum\Shop\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Laralum\Shop\Models\Order;
use Laralum\Shop\Models\Item;
use Auth;

class StatisticsController extends Controller
{
    /**
     * Show all the shop statistics.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $orders = Order::all();
        $number = 7;

        $statistics = [
            'items'             => Item::all(),
            'orders'            => $orders,
            'earnings'          => $orders->map(function ($order){ return $order->price(); })->sum(),
            'last_earnings'     => self::lastEarningsByDay($number),
            'last_orders'       => Order::whereDate('created_at', '>', date('Y-m-d', strtotime('-'.$number.' days')))->get(),
        ];

        return view('laralum_shop::statistics.index', ['statistics' => $statistics, 'number' => $number]);
    }

    /**
     * Show all the shop statistics.
     *
     * @return \Illuminate\Http\Response
     */
    public function filter(Request $request, $number = 7)
    {
        $orders = Order::all();

        if ($request->number) {
            $number = $request->number;
        }

        $statistics = [
            'items'             => Item::all(),
            'orders'            => $orders,
            'earnings'          => $orders->map(function ($order){ return $order->price(); })->sum(),
            'last_earnings'     => self::lastEarningsByDay($number),
            'last_orders'       => Order::whereDate('created_at', '>', date('Y-m-d', strtotime('-'.$number.' days')))->get(),
        ];

        return view('laralum_shop::statistics.index', ['statistics' => $statistics, 'number' => $number]);
    }

    /**
     * Return the latest earnings by day.
     *
     * @param int $d
     * @return \Illuminate\Http\Response
     */
    public function lastEarningsByDay($d = 7)
    {
        for ($days = []; count($days) < $d; array_push($days, (date('Y-m-d', strtotime('-'.count($days).' days')))));

        return collect($days)->reverse()->mapWithKeys(function($date){
            return [$date => Order::whereDate('created_at', '=', date('Y-m-d', strtotime($date)))->get()->map(function($order){
                return $order->price();
            })->sum()];
        });
    }
    
}
