<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

use Illuminate\Support\Facades\Auth;

use App\Models\Client;
use App\Models\User;


class ClientProfileRequest extends FormRequest
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
        //$user = Client::find($this->client);
        $user = Auth::user();
        $rules = [
            'name' => [
                'required'
            ],
            'last_name' => [
                'required'
            ],
            'username' => [
                'required',
                 Rule::unique('users', 'username')->ignore($user->id ?? -1)
            ],
            //document
            'province_id' => [
                'integer',
                'required',
                'exists:App\Models\Province,id'
            ],
            'address' => [
                'required'
            ],
            'birthday' => [
                'required',
                'date_format:Y-m-d'
            ],
            'gender_id' => [
                'required',
                'integer',
                'exists:App\Models\Gender,id'
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

            'name.required' => 'El nombre es requerido.',
            'last_name.required' => 'El apellido es requerido.',
            'username.required' => 'El nombre de usuario es requerido.',
            'username.unique' => 'Ya existe un usuario con el username ingresado.',
            'province_id.required' => 'La provincia es requerida.',
            'province_id.integer' => 'El formato de provincia debe ser entero.',
            'province_id.exists' => 'La provincia ingresada no existe.',
            'address.required' => 'La dirección es requerida.',
            'birthday.required' => 'La fecha de cumpleaños es requerido.',
            'birthday.date_format' => 'El formato de la fecha de cumpleaños es incorrecto (YYYY/mm/dd).',   
            'gender_id.required' => 'El Genero es requerido',
            'gender_id.integer' => 'El formato del genero debe ser entero.',
            'gender_id.exists' => 'El Genero ingresado no existe.'
            
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
