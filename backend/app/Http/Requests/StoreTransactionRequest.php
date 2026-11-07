<?php

namespace App\Http\Requests;

use App\Models\Category;
use App\Models\ChartOfAccount;
use App\Models\Transaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreTransactionRequest extends FormRequest
{
    /**
     * Menentukan izin otorisasi penyimpanan transaksi.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi input tanggal, COA, keterangan, dan nominal debit/credit.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'date' => ['required', 'date_format:Y-m-d'],
            'coa_id' => ['required', 'integer', 'exists:chart_of_accounts,id'],
            'description' => ['required', 'string', 'max:255'],
            'debit' => ['nullable', 'numeric', 'min:0', 'max:9999999999999.99'],
            'credit' => ['nullable', 'numeric', 'min:0', 'max:9999999999999.99'],
        ];
    }

    /**
     * Normalisasi default debit/credit ke 0 dan trim deskripsi.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'debit' => $this->input('debit') === null || $this->input('debit') === '' ? 0 : $this->input('debit'),
            'credit' => $this->input('credit') === null || $this->input('credit') === '' ? 0 : $this->input('credit'),
            'description' => is_string($this->input('description')) ? trim($this->input('description')) : $this->input('description'),
        ]);
    }

    /**
     * Validasi lanjutan: memastikan hanya salah satu dari debit atau credit yang bernilai > 0.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $debit = round((float) $this->input('debit', 0), 2);
                $credit = round((float) $this->input('credit', 0), 2);

                if ($debit <= 0 && $credit <= 0) {
                    $validator->errors()->add('debit', 'Either debit or credit must be greater than zero.');

                    return;
                }

                if ($debit > 0 && $credit > 0) {
                    $validator->errors()->add('debit', 'Only one of debit or credit may be greater than zero in a single transaction.');

                    return;
                }

                $coa = ChartOfAccount::find($this->input('coa_id'));
                if (! $coa) {
                    $validator->errors()->add('coa_id', 'The selected Chart of Account does not exist.');
                }
            },
        ];
    }
}
