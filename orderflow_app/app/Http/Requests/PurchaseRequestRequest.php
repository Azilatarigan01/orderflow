<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:3', 'max:200'],
            'description' => ['required', 'string', 'min:10'],
            'required_date' => ['required', 'date', 'after_or_equal:today'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_name' => ['required', 'string', 'max:200'],
            'items.*.specification' => ['nullable', 'string'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit' => ['required', 'string', 'max:30'],
            'items.*.estimated_unit_price' => ['required', 'numeric', 'min:1'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul pengadaan wajib diisi.',
            'title.min' => 'Judul pengadaan minimal 3 karakter.',
            'description.required' => 'Alasan & justifikasi kebutuhan bisnis wajib diisi.',
            'description.min' => 'Alasan kebutuhan bisnis minimal 10 karakter.',
            'required_date.required' => 'Tanggal barang dibutuhkan wajib diisi.',
            'required_date.after_or_equal' => 'Tanggal barang dibutuhkan tidak boleh di masa lalu.',
            'items.required' => 'Pengajuan harus memiliki minimal 1 baris item barang/jasa.',
            'items.min' => 'Pengajuan harus memiliki minimal 1 baris item barang/jasa.',
            'items.*.item_name.required' => 'Nama barang/jasa pada setiap baris wajib diisi.',
            'items.*.quantity.required' => 'Jumlah kuantitas wajib diisi.',
            'items.*.quantity.min' => 'Jumlah kuantitas minimal 1 unit.',
            'items.*.unit.required' => 'Satuan barang wajib dipilih atau diisi.',
            'items.*.estimated_unit_price.required' => 'Estimasi harga satuan wajib diisi.',
            'items.*.estimated_unit_price.min' => 'Estimasi harga satuan minimal Rp 1.',
            'attachment.max' => 'Ukuran berkas lampiran tidak boleh melebihi 5 MB.',
            'attachment.mimes' => 'Format berkas lampiran harus berupa PDF, JPG, PNG, Word, atau Excel.',
        ];
    }
}
