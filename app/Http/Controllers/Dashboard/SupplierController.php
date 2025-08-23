<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::latest()->paginate(10);
        return view('dashboard.suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('dashboard.suppliers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'balance' => 'nullable|numeric',
        ]);

        Supplier::create($request->all());

        return redirect()->route('dashboard.suppliers.index')
                         ->with('success', 'تم إنشاء المورد بنجاح');
    }

    public function edit(Supplier $supplier)
    {
        return view('dashboard.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'balance' => 'nullable|numeric',
        ]);

        $supplier->update($request->all());

        return redirect()->route('dashboard.suppliers.index')
                         ->with('success', 'تم تحديث المورد بنجاح');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return redirect()->route('dashboard.suppliers.index')
                         ->with('success', 'تم حذف المورد بنجاح');
    }
}
