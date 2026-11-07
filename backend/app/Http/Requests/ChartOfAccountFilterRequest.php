<?php

namespace App\Http\Requests;

use App\Models\ChartOfAccount;
use Illuminate\Foundation\Http\FormRequest;

class ChartOfAccountFilterRequest extends FormRequest
{
    /**
     * Menentukan izin otorisasi request filter COA.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi query parameter pencarian, relasi kategori, dan paginasi COA.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
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

