<?php

namespace App\Http\Requests;

use App\Enums\Currency;
use App\Enums\PaymentMethod;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'client_id' => ['required', 'integer', 'exists:clients,id'],

            'currency' => ['required', Rule::enum(Currency::class)],

            'payment_methods' => ['required', 'array', 'min:1', 'max:4'],
            'payment_methods.*.type' => ['required', Rule::enum(PaymentMethod::class)],
            'payment_methods.*.others_description' => ['required_if:payment_methods.*.type,99', 'nullable', 'string', 'min:3', 'max:100'],
            'payment_methods.*.amount' => ['required', 'numeric', 'min:0'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.name' => ['required', 'string', 'max:200'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.tax_percentage' => ['required', 'numeric', 'min:0', 'max:100'],

            'items.*.discount_enabled' => ['required', 'boolean'],
            'items.*.discount_type' => ['required_if:items.*.discount_enabled,true', 'nullable', 'string',
                Rule::in(['01', '02', '03', '04', '05', '06', '07', '08', '09', '99']),
            ],
            'items.*.discount_percentage' => ['required_if:items.*.discount_enabled,true', 'nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Debe agregar al menos un producto a la factura.',
            'items.min' => 'Debe agregar al menos un producto a la factura.',
            'items.*.product_id.exists' => 'Uno de los productos seleccionados no existe.',
            'items.*.quantity.min' => 'La cantidad debe ser mayor a cero.',
            'items.*.unit_price.min' => 'El precio unitario no puede ser negativo.',
            'items.*.discount_percentage.min' => 'El descuento no puede ser negativo.',
            'items.*.discount_percentage.max' => 'El descuento no puede superar el 100%.',
            'items.*.discount_type.in' => 'El tipo de descuento no es válido.',
            'currency.Illuminate\Validation\Rules\Enum' => 'La moneda seleccionada no es válida.',
            'payment_methods.min' => 'Debe indicar al menos un método de pago.',
            'payment_methods.max' => 'No puede haber más de 4 métodos de pago.',
            'payment_methods.*.type.Illuminate\Validation\Rules\Enum' => 'El tipo de método de pago no es válido.',
            'payment_methods.*.others_description.required_if' => 'La descripción es obligatoria cuando el método es "Otros".',
            'payment_methods.*.amount.required' => 'El monto es obligatorio para cada método de pago.',
            'payment_methods.*.amount.min' => 'El monto no puede ser negativo.',
        ];
    }
}
