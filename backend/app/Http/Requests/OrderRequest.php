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
            'client_id' => [
                'required',
				'integer',
                'exists:App\Models\Client,id'
            ],
            'shipping_state_id' => [
                'required',
				'integer',
                'exists:App\Models\ShippingState,id'
            ],
            'payment_state_id' => [
                'required',
				'integer',
                'exists:App\Models\PaymentState,id'
            ],
            'address_id' => [
                'required',
				'integer',
                'exists:App\Models\Address,id'
            ],
            'date' => [
                'date',
                'required'
            ],
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
            'product_color_id' => [
                'array',
                'required'
            ],
            'product_color_id.*' => [
                'required',
                'integer',
                'exists:App\Models\ProductColor,id'
            ],
            'price' => [
                'array',
                'required'
            ],
            'price.*' => [
                'required',
                'numeric'
            ],
            'quantity' => [
                'array',
                'required'
            ],
            'quantity.*' => [
                'required',
                'numeric'
            ],
            'province_id' => [
                'required',
                'integer',
                'exists:App\Models\Province,id'
            ],
            'default' => [
                'boolean',
                'required'
            ],
            'pse' => [
                'boolean',
                'required'
            ],
            'card_number' => [
                'numeric'
            ],
            'expired_date' => [
                'date'
            ],
            'cvv_code' => [
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
            'client_id.required' => 'El cliente es requerido.',
            'client_id.integer' => 'El formato del Id del cliente debe ser entero.',
            'client_id.exists' => 'El cliente ingresado no existe.',
         
            'shipping_state_id.required' => 'El Id de Estado de envío es requerido.',
            'shipping_state_id.integer' => 'El formato de Id de Estado de envío debe ser entero.',
            'shipping_state_id.exists' => 'El Id de Estado de envío ingresado no existe.',
            
            'payment_state_id.required' => 'El Id de Estado de Pago es requerido.',
            'payment_state_id.integer' => 'El formato de Id de Estado de Pago debe ser entero.',
            'payment_state_id.exists' => 'El Id de Estado de Pago ingresado no existe.',
            
            'address_id.required' => 'La dirección es requerida.',
            'address_id.integer' => 'El Id de dirección debe ser entero.',
            'address_id.exists' => 'La dirección ingresada no existe.',

            'date.required' => 'La fecha de la orden es requerida.',
            'date.date' => 'La fecha de la orden debe estar en formato fecha.',

            'subtotal.required' => 'El subtotal es requerido.',
            'subtotal.numeric' => 'El subtotal debe ser numérico.',

            'shipping_total.required' => 'El total de envío es requerido.',
            'shipping_total.numeric' => 'El total de envío debe ser numérico.',

            'tax.required' => 'El tax es requerido.',
            'tax.numeric' => 'El tax debe ser numérico.',

            'product_color_id.required' => 'El Id Product_color es requerido.',
            'product_color_id.array' => 'El Id Product_color debe ser array.',

            'product_color_id.*.required' => 'El Id Product_color es requerido.',
            'product_color_id.*.integer' => 'El Id Product_color debe ser entero.',
            'product_color_id.*.exists' => 'El Id Product_color ingresado no existe.',

            'price.required' => 'El Precio es requerido.',
            'price.array' => 'El Precio debe ser un array.',

            'price.*.required' => 'El precio es requerido.',
            'price.*.numeric' => 'El precio debe ser numérico.',

            'quantity.required' => 'La cantidad es requerida.',
            'quantity.array' => 'La cantidad debe ser un array.',

            'quantity.*.required' => 'La cantidad es requerida.',
            'quantity.*.numeric' => 'La cantidad debe ser numérico.',

            'province_id.required' => 'La provincia es requerida.',
            'province_id.integer' => 'El formato de provincia debe ser entero.',
            'province_id.exists' => 'La provincia ingresada no existe.',

            
            'default.required' => 'El campo default es requerido.',
            'default.boolean' => 'El formato del campo default debe ser boolean.',
            'pse.required' => 'El campo Pse es requerido.',
            'pse.boolean' => 'El formato del campo Pse debe ser boolean.',
            'card_number.numeric' => 'El número de la tarjeta debe ser numérico.',
            'expired_date.numeric' => 'La fecha de la expriracion de la tarjeta debe estar en formato fecha.',
            'cvv_code.numeric' => 'El código cvv debe ser numérico.',
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
