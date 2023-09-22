<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CategoryRequest extends FormRequest
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
            'name' => [
                'required'
            ]
        ];

        if (request('is_category')) {
            $rules['category_id'] = 'required|integer|exists:App\Models\Category,id';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'category_id.required' => 'La categoría es requerida',
            'category_id.integer' => 'El formato de categoría debe ser entero.',
            'category_id.exists' => 'La categoría ingresada no existe.',
            'name.required' => 'El nombre de categoría es requerido.'
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
