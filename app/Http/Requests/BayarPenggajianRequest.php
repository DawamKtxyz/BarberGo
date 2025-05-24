<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BayarPenggajianRequest extends FormRequest
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
            'id_gaji' => 'required|array|min:1',
            'id_gaji.*' => 'exists:penggajian,id_gaji',
            'bukti_transfer' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'id_gaji.required' => 'Pilih minimal satu data gaji',
            'id_gaji.array' => 'Format data tidak valid',
            'id_gaji.min' => 'Pilih minimal satu data gaji',
            'id_gaji.*.exists' => 'Data gaji tidak ditemukan',
            'bukti_transfer.required' => 'Bukti transfer harus diupload',
            'bukti_transfer.image' => 'File harus berupa gambar',
            'bukti_transfer.mimes' => 'Format gambar harus jpeg, png, jpg, atau gif',
            'bukti_transfer.max' => 'Ukuran file maksimal 2MB'
        ];
    }
}
