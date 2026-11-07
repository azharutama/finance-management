<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    /**
     * Menentukan izin otorisasi pembaruan kategori.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi pembaruan kategori dengan pengecualian ID kategori saat ini.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Category $category */
        $category = $this->route('category');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories', 'name')->ignore($category->id),
            ],
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

