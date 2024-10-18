<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ReferralRequest extends FormRequest
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
        $rules = [
           'supplier_id' => [
                'integer',
                'required',
                'exists:App\Models\Supplier,id'
            ],
            'product_color_id' => [
                'integer',
                'required',
                'exists:App\Models\ProductColor,id'
            ],
            'quantity' => [
                'integer',
                'required'
            ]
        ];

        return $rules;
    }

    public function messages()
    {
        return [
            'supplier_id.required' => 'El proveedor es requerido.',
            'supplier_id.integer' => 'El formato de proveedor debe ser entero.',
            'supplier_id.exists' => 'El proveedor ingresada no existe.',
            'product_color_id.required' => 'El producto es es requerido.',
            'product_color_id.integer' => 'El formato del producto debe ser entero.',
            'product_color_id.exists' => 'El producto ingresado no existe.',
            'quantity.required' => 'La cantidad es requerida.',
            'quantity.integer' => 'El formato de la cantidad debe ser entero.'
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
