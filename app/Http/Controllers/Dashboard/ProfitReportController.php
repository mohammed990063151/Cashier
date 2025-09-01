<?php
namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;

class ProfitReportController extends Controller
{
    // تقرير أرباح مفصل
    public function detailed()
    {
        $orders = Order::with('client','products')->get();

        return view('reports.profit.profit_detailed', compact('orders'));
    }

    // تقرير أرباح مجمل
    public function summary()
    {
        $orders = Order::with('products')->get();

        $totalSales = 0;
        $totalCost  = 0;

        foreach($orders as $order){
            foreach($order->products as $product){
                $totalSales += $product->pivot->sale_price * $product->pivot->quantity;
                $totalCost  += $product->purchase_price;
            }
        }

        $totalProfit = $totalSales - $totalCost;

        return view('reports.profit.profit_summary', compact('totalSales','totalCost','totalProfit'));
    }

    // نسبة أرباح المنتجات
    public function productRatio()
    {
        $products = Product::with('orders')->get();

        $productProfits = [];

        foreach($products as $product){
            $totalSales = $product->orders->sum(function($order) use ($product){
                return $order->products->find($product->id)->pivot->sale_price *
                       $order->products->find($product->id)->pivot->quantity;
            });

            $totalCost = $product->orders->sum(function($order) use ($product){
             return   $order->products->find($product->id)->purchase_price *
                       $order->products->find($product->id)->pivot->quantity;
                      
            });
//  return    $totalSales;
            $profit = $totalSales - $totalCost;
            $ratio = $totalSales ? ($profit  * 100 / $totalCost) : 0;

            $productProfits[] = [
                'product' => $product->name,
                'profit' => $profit,
                'ratio'  => round($ratio,2)
            ];
        }

        return view('reports.profit.profit_ratio', compact('productProfits'));
    }
}
