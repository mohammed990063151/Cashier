<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePurchaseInvoiceRequest extends FormRequest
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
        ];
    }

    public function messages(): array
    {
        return (new StorePurchaseInvoiceRequest)->messages();
    }
}
