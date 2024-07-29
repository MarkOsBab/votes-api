<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CreateVoterRequest extends FormRequest
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
            'name' => ['required', 'min:3'],
            'lastName' => ['required', 'min:3'],
            'document' => ['required', 'unique:voters,document'],
            'dob' => ['required', 'date'],
            'is_candidate' => ['required', Rule::in([1, 0])],
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'nombre',
            'lastName' => 'apellido',
            'document' => 'documento',
            'dob' => 'fecha de nacimiento',
            'is_candidate' => 'candidato o votante'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'El :attribute es requerido',
            'name.min' => 'El :attribute debe contener mínimo 3 caracteres',
            'lastName.required' => 'El :attribute es requerido',
            'lastName.min' => 'El :attribute debe contener mínimo 3 caracteres',
            'document.required' => 'El :attribute es requerido',
            'document.unique' => 'El :attribute ya está registrado',
            'is_candidate.required' => 'El :attribute es requerido',
            'is_candidate.required' => 'El :attribute tiene un formato inválido',
        ];
    }

    public function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $response = response()->json($validator->errors(), Response::HTTP_BAD_REQUEST);
        throw new ValidationException($validator, $response);
    }
}
