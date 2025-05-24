<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePenggajianRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'id_pesanan' => 'required|exists:pesanan,id',
            'id_barber' => 'required|exists:tukang_cukur,id',
            'id_pelanggan' => 'required|exists:pelanggan,id',
            'total_bayar' => 'required|numeric|min:0',
            'potongan' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:lunas,belum lunas'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'id_pesanan.required' => 'ID pesanan harus diisi',
            'id_pesanan.exists' => 'Pesanan tidak ditemukan',
            'id_barber.required' => 'Barber harus dipilih',
            'id_barber.exists' => 'Barber tidak ditemukan',
            'id_pelanggan.required' => 'Pelanggan harus dipilih',
            'id_pelanggan.exists' => 'Pelanggan tidak ditemukan',
            'total_bayar.required' => 'Total bayar harus diisi',
            'total_bayar.numeric' => 'Total bayar harus berupa angka',
            'total_bayar.min' => 'Total bayar tidak boleh negatif',
            'potongan.numeric' => 'Potongan harus berupa angka',
            'potongan.min' => 'Potongan tidak boleh negatif',
            'status.in' => 'Status harus lunas atau belum lunas'
        ];
    }
}
