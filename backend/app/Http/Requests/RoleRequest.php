<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RoleRequest extends FormRequest
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
                'required',
                 Rule::unique('roles', 'name')->ignore($this->role)
            ],
            'permissions' => [
                'required',
                'array',
                'exists:Spatie\Permission\Models\Permission,name'
            ]
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'El nombre es requerido.',
            'permissions.required' => 'Los permisos son requeridos.',
            'permissions.array' => 'El formato de permisos no es permitido.',
            'permissions.exists' => 'El permiso ingresado no existe.'
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
