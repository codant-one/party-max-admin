<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StatusServiceRequest extends FormRequest
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
            'favourite' => 'boolean',
            'discarded'   => 'boolean',
            'archived'  => ['boolean', 'required_without_all:favourite,discarded']
        ];
    }

    public function messages()
    {
        return [
            'favourite.boolean' => 'El campo favorito debe ser booleano.',
            'discarded.boolean' => 'El campo descartado debe ser booleano.',
            'archived.boolean' => 'El campo archivado debe ser booleano.',
            'archived.required_without_all' => 'Al menos uno de los campos favorito o descartado o archivado es requerido.'
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
