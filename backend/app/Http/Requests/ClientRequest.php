<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

use App\Models\Client;

class ClientRequest extends FormRequest
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
        $client = Client::find($this->client);
        $rules = [
            'gender_id' => [
                'required',
                'integer',
                'exists:App\Models\Gender,id'
            ],
            'birthday' => [
                'required',
                'date_format:Y-m-d'
            ],

            'name' => [
                'required'
            ],
            'last_name' => [
                'required'
            ],
            'email' => [
                'required_if:user_id,<>'.($client->user_id ?? -1),
                Rule::unique('users', 'email')->ignore($client->user_id ?? -1)
            ],
            // 'password' => [
            //     ($this->user) ? 'nullable' : 'required'
            // ],
            'province_id' => [
                'integer',
                'required',
                'exists:App\Models\Province,id'
            ],
            'username' => [
                'required',
                Rule::unique('users', 'username')->ignore($client->user_id ?? -1)
            ],
            'phone' => [
                'required'
            ],
            'address' => [
                'required'
            ]  
        ];

        if (request('is_client')) {
            $rules['client_id'] = 'required|integer|exists:App\Models\Client,id';
        }

        return $rules;
    }


    public function messages()
    {
        return [
            'client_id.required' => 'El Cliente es requerido',
            'client_id.integer' => 'El formato del cliente debe ser entero.',
            'client_id.exists' => 'El Cliente ingresado no existe.',
            'gender_id.required' => 'El Genero es requerido',
            'gender_id.integer' => 'El formato del genero debe ser entero.',
            'gender_id.exists' => 'El Genero ingresado no existe.',
            'birthday.required' => 'El nombre del Cliente es requerido.',
            'birthday.date_format' => 'El formato de la fecha de cumpleaños es incorrecto (YYYY/mm/dd).',

            'name.required' => 'El nombre es requerido.',
            'last_name.required' => 'El apellido es requerido.',
            'email.required' => 'El correo electrónico es requerido.',
            'email.email' => 'El formato de correo electrónico no es permitido.',
            'email.unique' => 'Ya existe un usuario con el correo electrónico ingresado.',
            // 'password.required' => 'La contraseña es requerida.',
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
