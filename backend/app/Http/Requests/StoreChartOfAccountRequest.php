<?php

namespace App\Http\Requests;

use App\Models\ChartOfAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreChartOfAccountRequest extends FormRequest
{
    /**
     * Menentukan izin otorisasi penyimpanan akun COA.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi pembuatan COA baru (kode unik, nama, dan relasi kategori).
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:30', Rule::unique('chart_of_accounts', 'code')],
            'name' => ['required', 'string', 'max:150'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
        ];
    }

    /**
     * Sanitasi dan trim spasi pada input kode dan nama COA.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('code') && is_string($this->input('code'))) {
            $this->merge(['code' => trim($this->input('code'))]);
        }
        if ($this->has('name') && is_string($this->input('name'))) {
            $this->merge(['name' => trim($this->input('name'))]);
        }
    }
}

