<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProductRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required'
            ],
            'sku' => [
                'required'
            ],
            'price' => [
                'required'
            ],
            'price_for_sale' => [
                'required'
            ]
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'El nombre del producto es requerido.',
            'sku.required' => 'El sku requerido',
            'price.required' => 'El precio es requerido.',
            'price_for_sale.required' => 'El precio para la venta es requerido.',
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
