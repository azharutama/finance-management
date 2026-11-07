<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryFilterRequest extends FormRequest
{
    /**
     * Menentukan izin otorisasi request filter kategori.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi query parameter filter kategori.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'all' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Normalisasi nilai boolean sebelum divalidasi.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('all') && $this->input('all') !== null && $this->input('all') !== '') {
            $this->merge([
                'all' => filter_var($this->input('all'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            ]);
        }
    }
}

