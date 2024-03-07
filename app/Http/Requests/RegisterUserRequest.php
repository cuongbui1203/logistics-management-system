<?php

namespace App\Http\Requests;

// use App\Http\Requests\
use Illuminate\Validation\Rules\Password;

class RegisterUserRequest extends FormRequest
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
            'name'=>'required',
            'username'=>'required|unique:users,username',
            'dob'=>'required|date',
            'password'=>[
                'required',
                'confirmed',
                Password::min(8)->letters(),
            ],
            'email'=>'required|email|unique:users,email',
            'image'=>'image',
        ];
    }
}