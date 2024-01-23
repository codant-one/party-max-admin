<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class AddressRequest extends FormRequest
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
            'addresses_type_id' => [
                'required',
                'integer',
                'exists:App\Models\AddressesType,id'
            ],
            'province_id' => [
                'integer',
                'required',
                'exists:App\Models\Province,id'
            ],
            'phone' => [
                'regex:/^[0-9\-\+\(\) ]+$/'
            ],
            'default' => [
                'integer',
                'required'
            ]
        ];
    }

    public function messages()
    {
        return [
            'client_id.required' => 'El cliente es requerido.',
            'client_id.integer' => 'El formato de cliente debe ser entero.',
            'client_id.exists' => 'El cliente ingresado no existe.',
            'addresses_type_id.required' => 'El tipo de dirección es requerida.',
            'addresses_type_id.integer' => 'El formato del tipo de dirección debe ser entero.',
            'addresses_type_id.exists' => 'El tipo de dirección ingresado no existe.',
            'province_id.required' => 'La provincia es requerida.',
            'province_id.integer' => 'El formato de provincia debe ser entero.',
            'province_id.exists' => 'La provincia ingresada no existe.',
            'phone.regex' => 'El teléfono debe contener solo números, espacios y los caracteres - + ( )',
            'default.required' => 'El campo default es requerido.',
            'default.integer' => 'El formato del campo default debe ser entero.'
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
