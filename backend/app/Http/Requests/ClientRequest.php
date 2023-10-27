<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

use App\Http\Requests\UserRequest;

class ClientRequest extends UserRequest
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
        $rules = [
            'gender_id' => [
                'required',
                'integer',
                'exists:App\Models\Gender,id'
            ],
            'birthday' => [
                'required',
                'date_format:Y-m-d'
            ]
        ];

        if (request('is_client')) {
            $rules['client_id'] = 'required|integer|exists:App\Models\Client,id';
        }

        return array_merge(parent::rules(), $rules);
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
            'birthday.date_format' => 'El formato de la fecha de cumpleaños es incorrecto (YYYY/mm/dd).'
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
