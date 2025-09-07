<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ClientController extends Controller
{
    public function index(Request $request)
    {

        return view('dashboard.clients.index');

    }//end of index

    public function create()
    {
        return view('dashboard.clients.create');

    }//end of create

    public function store(Request $request)
    {
     $request->validate([
    'name' => 'required',
    'phone' => 'required|array|min:1',
    'phone.0' => 'required',
    'address' => 'required',
], [
    'name.required' => 'اسم العميل مطلوب.',
    'phone.required' => 'رقم الهاتف مطلوب.',
    'phone.array' => 'يجب أن يكون رقم الهاتف في شكل قائمة (مصفوفة).',
    'phone.min' => 'يجب إدخال رقم هاتف واحد على الأقل.',
    'phone.0.required' => 'الرقم الأول للهاتف مطلوب.',
    'address.required' => 'العنوان مطلوب.',
]);


        $request_data = $request->all();
        $request_data['phone'] = array_filter($request->phone);

        Client::create($request_data);

        session()->flash('success', __('تم اضافة العميل بنجاح'));
        return redirect()->route('dashboard.clients.index');

    }//end of store

    public function edit(Client $client)
    {
        return view('dashboard.clients.edit', compact('client'));

    }//end of edit

    public function update(Request $request, Client $client)
    {
      $request->validate([
    'name' => 'required',
    'phone' => 'required|array|min:1',
    'phone.0' => 'required',
    'address' => 'required',
], [
    'name.required' => 'اسم العميل مطلوب.',
    'phone.required' => 'رقم الهاتف مطلوب.',
    'phone.array' => 'يجب أن يكون رقم الهاتف في شكل قائمة (مصفوفة).',
    'phone.min' => 'يجب إدخال رقم هاتف واحد على الأقل.',
    'phone.0.required' => 'الرقم الأول للهاتف مطلوب.',
    'address.required' => 'العنوان مطلوب.',
]);


        $request_data = $request->all();
        $request_data['phone'] = array_filter($request->phone);

        $client->update($request_data);
        session()->flash('success', __('تم تعديل العميل بنجاح'));
        return redirect()->route('dashboard.clients.index');

    }//end of update

    public function destroy(Client $client)
    {
        $client->delete();
        session()->flash('success', __('تم حذف العميل بنجاح'));
        return redirect()->route('dashboard.clients.index');

    }//end of destroy
    public function restoreClient($id)
{
    $client = Client::withTrashed()->findOrFail($id);
    $client->restore();

    // إذا تريد استرجاع الطلبات المرتبطة (اختياري)
    foreach ($client->orders()->withTrashed()->get() as $order) {
        $order->restore();
        // إذا لديك تعديل على المنتجات كما في مثالك، ضعه هنا
        foreach ($order->products as $product) {
            $product->update([
                'stock' => $product->stock - $product->pivot->quantity
            ]);
        }
    }

    session()->flash('success', "تم استرجاع العميل: #{$client->id}");
    return redirect()->back();
}


}//end of controller
