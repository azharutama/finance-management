<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Menentukan izin otorisasi request login.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi email dan password login.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:1'],
        ];
    }
}

