<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class OrderRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'sub_total' => [
                'numeric',
                'required'
            ],
            'shipping_total' => [
                'numeric',
                'required'
            ],
            'tax' => [
                'numeric',
                'required'
            ],
            'province_id' => [
                'required',
                'integer',
                'exists:App\Models\Province,id'
            ],
            'card_number' => [
                'numeric'
            ],
            'phone' => [
                'regex:/^[0-9\-\+\(\) ]+$/'
            ]
        ];
    }

    public function messages()
    {
        return [
            'subtotal.required' => 'El subtotal es requerido.',
            'subtotal.numeric' => 'El subtotal debe ser numérico.',

            'shipping_total.required' => 'El total de envío es requerido.',
            'shipping_total.numeric' => 'El total de envío debe ser numérico.',

            'tax.required' => 'El tax es requerido.',
            'tax.numeric' => 'El tax debe ser numérico.',
            
            'province_id.required' => 'La provincia es requerida.',
            'province_id.integer' => 'El formato de provincia debe ser entero.',
            'province_id.exists' => 'La provincia ingresada no existe.',

            'card_number.numeric' => 'El número de la tarjeta debe ser numérico.',
            'phone.regex' => 'El teléfono debe contener solo números, espacios y los caracteres - + ( )'
        ];
    }

    /**
    * Get the error messages for the defined validation rules.*
    * @return array
    */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'feedback' => 'params_validation_failed',
            'message' => $validator->errors()
        ], 400));
    }
}
