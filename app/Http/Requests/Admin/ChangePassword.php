<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class ChangePassword extends FormRequest
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
            'currentPassword' => ['required'],
            'newPassword' => ['required', 'min:6', 'max:16', 'regex:/[A-Z]/', 'regex:/[!@#$%^&*(),.?":{}|<>]/'],
        ];
    }

    public function attributes()
    {
        return [
            'currentPassword' => 'contraseña actual',
            'newPassword' => 'nueva contraseña',
        ];
    }

    public function messages()
    {
        return [
            'currentPassword.required' => 'La :attribute es requerida',
            'newPassword.required' => 'La :attribute es requerida',
            'newPassword.min' => 'La :attribute debe tener mínimo 6 caracteres',
            'newPassword.max' => 'La :attribute debe tener máximo 16 caracteres',
            'newPassword.regex' => 'La :attribute debe contener al menos una letra mayúscula y un símbolo',
        ];
    }

    public function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $response = response()->json($validator->errors(), Response::HTTP_BAD_REQUEST);
        throw new ValidationException($validator, $response);
    }
}
