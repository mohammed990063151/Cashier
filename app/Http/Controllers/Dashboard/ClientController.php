<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Client;
use App\Services\OpeningBalanceService;
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

    public function store(Request $request, OpeningBalanceService $openingBalances)
    {
        $request->validate([
            'name' => 'required',
            'phone' => 'required|array|min:1',
            'phone.0' => 'required',
            'address' => 'required',
            'opening_amount' => 'nullable|numeric|min:0',
            'opening_paid' => 'nullable|numeric|min:0',
            'opening_date' => 'nullable|date',
            'opening_reference' => 'nullable|string|max:80',
            'opening_details' => 'nullable|string|max:2000',
            'opening_photo' => 'nullable|image|max:5120',
        ], [
            'name.required' => 'اسم العميل مطلوب.',
            'phone.required' => 'رقم الهاتف مطلوب.',
            'phone.array' => 'يجب أن يكون رقم الهاتف في شكل قائمة (مصفوفة).',
            'phone.min' => 'يجب إدخال رقم هاتف واحد على الأقل.',
            'phone.0.required' => 'الرقم الأول للهاتف مطلوب.',
            'address.required' => 'العنوان مطلوب.',
            'opening_amount.numeric' => 'مبلغ الحساب القديم يجب أن يكون رقماً.',
            'opening_paid.numeric' => 'المدفوع سابقاً يجب أن يكون رقماً.',
            'opening_photo.image' => 'أرفق صورة فاتورة فقط.',
            'opening_photo.max' => 'حجم الصورة يجب ألا يتجاوز 5 ميغابايت.',
        ]);

        $amount = round((float) $request->input('opening_amount', 0), 2);
        $paid = round((float) $request->input('opening_paid', 0), 2);
        if ($paid > $amount + 0.009) {
            return back()->withInput()->withErrors([
                'opening_paid' => 'المدفوع سابقاً لا يمكن أن يكون أكبر من إجمالي الحساب القديم.',
            ]);
        }

        $client = Client::create([
            'name' => $request->name,
            'phone' => array_values(array_filter($request->phone)),
            'address' => $request->address,
        ]);

        $message = 'تم إضافة العميل بنجاح';
        if ($amount > 0.009) {
            $opening = $openingBalances->create(
                $client,
                $amount,
                $paid,
                $request->input('opening_reference'),
                $request->input('opening_details'),
                $request->input('opening_date'),
                $request->file('opening_photo')
            );
            $message .= '، وسُجّل حسابه القديم '.$opening->order_number.' بمبلغ '.number_format($amount, 2).' ج.س';
            if ($opening->remaining > 0) {
                $message .= ' والمتبقي '.number_format($opening->remaining, 2).' ج.س';
            }
        }

        session()->flash('success', $message);

        return redirect()->route('dashboard.clients.index');
    }

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
                'stock' => \App\Support\DecimalMath::sub(
                    (float) $product->stock,
                    (float) $product->pivot->quantity
                ),
            ]);
        }
    }

    session()->flash('success', "تم استرجاع العميل: #{$client->id}");
    return redirect()->back();
}


}//end of controller
