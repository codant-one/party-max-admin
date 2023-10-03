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
            'title' => [
                'required'
            ]
        ];

        if (request('is_faq')) {
            $rules['faq_id'] = 'required|integer|exists:App\Models\Faq,id';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'faq_id.required' => 'La categoría es requerida',
            'faq_id.integer' => 'El formato de categoría debe ser entero.',
            'faq_id.exists' => 'La categoría ingresada no existe.',
            'title.required' => 'El nombre de categoría es requerido.'
            'description.required' => 'El nombre de categoría es requerido.'
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
