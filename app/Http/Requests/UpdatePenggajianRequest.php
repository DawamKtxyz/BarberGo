<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePenggajianRequest extends FormRequest
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
            'potongan' => 'required|numeric|min:0',
            'status' => 'required|in:lunas,belum lunas'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'potongan.required' => 'Potongan harus diisi',
            'potongan.numeric' => 'Potongan harus berupa angka',
            'potongan.min' => 'Potongan tidak boleh negatif',
            'status.required' => 'Status harus dipilih',
            'status.in' => 'Status harus lunas atau belum lunas'
        ];
    }
}
