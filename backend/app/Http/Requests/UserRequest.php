<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserRequest extends FormRequest
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
            'last_name' => [
                'required'
            ],
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($this->user)
            ],
            'password' => [
                ($this->user) ? 'nullable' : 'required'
            ],
            // 'roles' => [
            //     'required',
            //     'array',
            //     'exists:Spatie\Permission\Models\Role,name'
            // ],
            'province_id' => [
                'integer',
                'required',
                'exists:App\Models\Province,id'
            ],
            'username' => [
                'required',
                Rule::unique('users', 'username')->ignore($this->user)
            ],
            'phone' => [
                'required'
            ],
            'address' => [
                'required'
            ]         
        ];

        //Si NO es cliente se evalua la existencia del Rol.
        if (!request('is_client')) {
            $rules['roles'] = 'required|array|exists:Spatie\Permission\Models\Role,name';
        }
    }

    public function messages()
    {
        return [
            'name.required' => 'El nombre es requerido.',
            'last_name.required' => 'El apellido es requerido.',
            'email.required' => 'El correo electrónico es requerido.',
            'email.email' => 'El formato de correo electrónico no es permitido.',
            'email.unique' => 'Ya existe un usuario con el correo electrónico ingresado.',
            'password.required' => 'La contraseña es requerida.',
            'roles.required' => 'El rol es requerido.',
            'roles.array' => 'El formato de roles no es permitido.',
            'roles.exists' => 'El rol ingresado no existe.',
            'province_id.required' => 'La provincia es requerida.',
            'province_id.integer' => 'El formato de provincia debe ser entero.',
            'province_id.exists' => 'La provincia ingresada no existe.',
            'username.required' => 'El nombre de usuario es requerido.',
            'username.unique' => 'Ya existe un usuario con el username ingresado.',
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
