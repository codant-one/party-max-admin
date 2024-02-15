<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

use App\Models\Supplier;

class SupplierRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $supplier = Supplier::find($this->supplier);

        $rules = [
            'name' => [
                'required'
            ],
            'last_name' => [
                'required'
            ],
            'email' => [
                'required_if:user_id,<>'.($supplier->user_id ?? -1),
                Rule::unique('users', 'email')->ignore($supplier->user_id ?? -1)
            ],
            'province_id' => [
                'integer',
                'required',
                'exists:App\Models\Province,id'
            ],
            'phone' => [
                'required'
            ],
            'address' => [
                'required'
            ]  
        ];


        return $rules;
    }

    public function messages()
    {
        return [
            'supplier_id.required' => 'El Proveedor es requerido',
            'supplier_id.integer' => 'El formato del proveedor debe ser entero.',
            'supplier_id.exists' => 'El Proveedor ingresado no existe.',
            'name.required' => 'El nombre es requerido.',
            'last_name.required' => 'El apellido es requerido.',
            'email.required' => 'El correo electrónico es requerido.',
            'email.email' => 'El formato de correo electrónico no es permitido.',
            'email.unique' => 'Ya existe un usuario con el correo electrónico ingresado.',
            'roles.required' => 'El rol es requerido.',
            'roles.array' => 'El formato de roles no es permitido.',
            'roles.exists' => 'El rol ingresado no existe.',
            'province_id.required' => 'La provincia es requerida.',
            'province_id.integer' => 'El formato de provincia debe ser entero.',
            'province_id.exists' => 'La provincia ingresada no existe.',
            'phone.required' => 'El teléfono es requerido.',
            'address.required' => 'La dirección es requerida.'
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
