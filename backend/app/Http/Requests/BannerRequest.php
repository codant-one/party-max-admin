<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class BannerRequest extends FormRequest
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
            ],
            'banner' => [
                'required'
            ],
            'banner_2' => [
                'required'
            ],
            'banner_3' => [
                'required'
            ],
            'banner_4' => [
                'required'
            ]
        ];

        return $rules;
    }

    public function messages()
    {
        return [
            'name.required' => 'El nombre de banner es requerido.',
            'banner.required' => 'El banner es requerido.',
            'banner_2.required' => 'El banner 2 es requerido.',
            'banner_3.required' => 'El banner 3 es requerido.',
            'banner_4.required' => 'El banner 4 es requerido.'
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
