<?php

namespace App\Http\Requests;

use App\Models\Vendor;
use Illuminate\Foundation\Http\FormRequest;

class QuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->hasRole('procurement') || auth()->user()->hasRole('admin'));
    }

    protected function prepareForValidation(): void
    {
        // Clean shipping_cost and tax_amount
        $shipping = preg_replace('/[^0-9]/', '', (string) $this->input('shipping_cost', '0'));
        $tax = preg_replace('/[^0-9]/', '', (string) $this->input('tax_amount', '0'));

        $this->merge([
            'shipping_cost' => $shipping !== '' ? (float) $shipping : 0,
            'tax_amount' => $tax !== '' ? (float) $tax : 0,
        ]);

        if ($this->has('items') && is_array($this->items)) {
            $cleanedItems = [];
            foreach ($this->items as $index => $item) {
                if (isset($item['unit_price'])) {
                    $cleanPrice = preg_replace('/[^0-9]/', '', (string) $item['unit_price']);
                    $item['unit_price'] = $cleanPrice !== '' ? (float) $cleanPrice : 0;
                }
                $cleanedItems[$index] = $item;
            }
            $this->merge(['items' => $cleanedItems]);
        }
    }

    public function rules(): array
    {
        return [
            'vendor_id' => [
                'required',
                'exists:vendors,id',
                function ($attribute, $value, $fail) {
                    $vendor = Vendor::find($value);
                    if ($vendor && !$vendor->is_active) {
                        $fail('Vendor yang dipilih berstatus nonaktif dan tidak dapat digunakan.');
                    }
                },
            ],
            'quotation_number' => ['nullable', 'string', 'max:100'],
            'shipping_cost' => ['required', 'numeric', 'min:0'],
            'tax_amount' => ['required', 'numeric', 'min:0'],
            'estimated_delivery_days' => ['required', 'integer', 'min:1'],
            'warranty_months' => ['nullable', 'integer', 'min:0'],
            'warranty_info' => ['nullable', 'string', 'max:150'],
            'valid_until' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx', 'max:10240'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.specification' => ['nullable', 'string'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit' => ['required', 'string', 'max:50'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'vendor_id.required' => 'Pilihan vendor wajib ditentukan.',
            'vendor_id.exists' => 'Vendor yang dipilih tidak terdaftar di sistem.',
            'estimated_delivery_days.required' => 'Estimasi waktu pengiriman wajib diisi.',
            'estimated_delivery_days.min' => 'Estimasi waktu pengiriman minimal 1 hari kerja.',
            'items.required' => 'Penawaran harus mencakup item barang/jasa.',
            'items.*.item_name.required' => 'Nama barang/jasa pada baris item wajib diisi.',
            'items.*.unit_price.required' => 'Harga satuan penawaran vendor wajib diisi.',
            'attachment.max' => 'Ukuran berkas penawaran tidak boleh melebihi 10MB.',
            'attachment.mimes' => 'Format berkas lampiran harus berupa PDF, gambar (JPG/PNG), Office Doc, atau Excel.',
        ];
    }
}
