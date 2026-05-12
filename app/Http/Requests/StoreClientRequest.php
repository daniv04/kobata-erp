<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'id_number_type' => ['required', 'string', 'in' => ['01', '02', '03', '04']],
            'id_number' => ['required', 'string', 'max:20', 'unique:clients,id_number'],
            'hacienda_name' => ['required', 'string', 'min:5', 'max:100'],
            'economic_activity_code' => ['nullable', 'string', 'max:6'],
            'economic_activity_description' => ['nullable', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:160'],
            'phone' => ['required', 'string', 'max:20'],
            'province_id' => ['required', 'exists:provinces,id'],
            'canton_id' => ['required', 'exists:cantons,id'],
            'district_id' => ['required', 'exists:districts,id'],
            'neighborhood_id' => ['nullable', 'exists:neighborhoods,id'],
            'address' => ['required', 'string', 'min:5', 'max:250'],
        ];
    }
}
