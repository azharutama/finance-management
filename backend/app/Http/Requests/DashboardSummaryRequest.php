<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DashboardSummaryRequest extends FormRequest
{
    /**
     * Menentukan izin otorisasi request ringkasan dashboard.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi format parameter bulan ('YYYY-MM').
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'month' => ['nullable', 'date_format:Y-m'],
        ];
    }
}

