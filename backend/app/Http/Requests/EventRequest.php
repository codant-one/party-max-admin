<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class EventRequest extends FormRequest
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
        $rules = [
            'title' => [
                'required'
            ],
            'extendedProps.calendar' => [
                'required',
                'exists:App\Models\Category,name'
            ],
            'start' => [
                'required'
            ],
            'end' => [
                'required'
            ]

        ];
        
        return $rules;
    }

    public function messages()
    {
        return [
            'title.required' => 'El titulo de la pregunta es requerido',
            'extendedProps.calendar.required' => 'La categoría es requerida',
            'extendedProps.calendar.exists' => 'La categoría no existe',
            'start.required' => 'La fecha de inicio del evento es requerida',
            'end.required' => 'La fecha final del evento es requerida',
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
