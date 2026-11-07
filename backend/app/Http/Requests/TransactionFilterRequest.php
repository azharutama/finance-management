<?php

namespace App\Http\Requests;

use App\Models\Transaction;
use Illuminate\Foundation\Http\FormRequest;

class TransactionFilterRequest extends FormRequest
{
    /**
     * Menentukan izin otorisasi filter transaksi.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi filter transaksi (pencarian, rentang tanggal, COA, kategori, dan paginasi).
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'coa_id' => ['nullable', 'integer', 'exists:chart_of_accounts,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}

