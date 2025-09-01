<?php
namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\PurchaseInvoiceDetail;
use App\Models\PriceHistory;
use Illuminate\Support\Facades\DB;

class InventoryReportController extends Controller
{
    // public function index()
    // {
    //     return view('reports.inventory.inventory');
    // }
    public function index(Request $request)
{
    // فلترة بالتاريخ لو حابب
    $from = $request->get('from');
    $to   = $request->get('to');
$products = Product::all();
$productsNames = $products->pluck('name');
$productsTotals = $products->map(fn($p) => $p->stock * $p->purchase_price);
    $categories = \App\Models\Category::with('products')->get();

$categoriesNames = $categories->pluck('name');
$categoriesTotals = $categories->map(function($cat) {
    return $cat->products->sum(function($p) {
        return $p->stock * $p->purchase_price;
    });
});
    $detailed = Product::with('category')
        ->when($from && $to, function($q) use ($from,$to){
            $q->whereBetween('created_at', [$from, $to]);
        })
        ->get();

    $summary = $detailed->sum(fn($item) => $item->stock * $item->purchase_price);

    $priceChanges = PriceHistory::with('product')
        ->latest()
        ->get();

    return view('reports.inventory.inventory', compact('detailed','summary','priceChanges','categoriesNames','categoriesTotals','productsNames','productsTotals'));
}


    public function getData(Request $request)
    {
        $type = $request->type; // summary / details / prices
        $from = $request->from;
        $to = $request->to;

        if ($type === 'summary') {
            // جرد مجمل
            $data = Product::select('id','name','stock','purchase_price','sale_price')->get();
        } elseif ($type === 'details') {
            // جرد مفصل (حركات الصنف)
            $data = DB::table('purchase_invoice_details as pid')
                ->join('products as p', 'p.id','=','pid.product_id')
                ->join('purchase_invoices as pi','pi.id','=','pid.purchase_invoice_id')
                ->when($from && $to, function($q) use ($from, $to){
                    $q->whereBetween('pi.invoice_date', [$from, $to]);
                })
                ->select('p.name','pid.quantity','pid.price','pi.invoice_date','pi.supplier_id')
                ->get();
        } else {
            // تغير الأسعار
            $data = DB::table('purchase_invoice_details as pid')
                ->join('products as p', 'p.id','=','pid.product_id')
                ->join('purchase_invoices as pi','pi.id','=','pid.purchase_invoice_id')
                ->when($from && $to, function($q) use ($from, $to){
                    $q->whereBetween('pi.invoice_date', [$from, $to]);
                })
                ->select('p.name','pid.price','pi.invoice_date')
                ->orderBy('pi.invoice_date','desc')
                ->get();
        }

        return response()->json(['data' => $data]);
    }
}
