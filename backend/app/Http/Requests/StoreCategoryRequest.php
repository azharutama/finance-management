<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    /**
     * Menentukan izin otorisasi penyimpanan kategori.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi pembuatan kategori baru (nama wajib unik).
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('categories', 'name')],
        ];
    }

    /**
     * Sanitasi dan trim spasi pada input nama kategori.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('name') && is_string($this->input('name'))) {
            $this->merge(['name' => trim($this->input('name'))]);
        }
    }
}

