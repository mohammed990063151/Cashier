<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'supplier_id' => 'required|exists:suppliers,id',
            'invoice_date' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.purchase_unit' => 'required|string|max:20',
            'items.*.entered_qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0.01',
            'paid' => 'nullable|numeric|min:0',
            'payment_due_at' => 'nullable|date',
            'payment_notes' => 'nullable|string|max:1000',
            'installments' => 'nullable|array',
            'installments.*.amount' => 'nullable|numeric|min:0.01',
            'installments.*.due_at' => 'nullable|date',
            'installments.*.notes' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'supplier_id.required' => 'اختر المورد.',
            'supplier_id.exists' => 'المورد غير موجود.',
            'items.required' => 'أضف منتجاً واحداً على الأقل.',
            'items.min' => 'أضف منتجاً واحداً على الأقل.',
            'items.*.product_id.required' => 'اختر المنتج في كل سطر.',
            'items.*.entered_qty.min' => 'الكمية يجب أن تكون 1 على الأقل.',
            'items.*.price.min' => 'سعر الوحدة يجب أن يكون أكبر من صفر.',
            'paid.min' => 'المدفوع لا يمكن أن يكون سالباً.',
        ];
    }
}
