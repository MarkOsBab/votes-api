<?php

namespace App\Http\Requests\Vote;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StoreRequest extends FormRequest
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
        return [
            'document' => ['required', Rule::exists('voters', 'document')],
            'candidate' => ['required', Rule::exists('voters', 'id')->where('is_candidate', 1)]
        ];
    }

    public function attributes()
    {
        return [
            'document' => 'documento',
            'candidate' => 'candidato'
        ];
    }

    public function messages()
    {
        return [
            'document.required' => 'El :attribute es requerido',
            'document.exists' => 'El :attribute no existe',
            'candidate.required' => 'El :attribute es requerido',
            'candidate.exists' => 'El :attribute no existe o no es candidato',
        ];
    }

    public function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $response = response()->json($validator->errors(), Response::HTTP_BAD_REQUEST);
        throw new ValidationException($validator, $response);
    }
}
