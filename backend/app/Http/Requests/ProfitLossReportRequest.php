<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Validator;

class ProfitLossReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'start_month' => ['nullable', 'date_format:Y-m'],
            'end_month' => ['nullable', 'date_format:Y-m', 'after_or_equal:start_month'],
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                if ($this->filled('start_month') && $this->filled('end_month')) {
                    $start = Carbon::createFromFormat('Y-m', $this->input('start_month'))->startOfMonth();
                    $end = Carbon::createFromFormat('Y-m', $this->input('end_month'))->startOfMonth();
                    if ($start->diffInMonths($end) > 36) {
                        $validator->errors()->add('end_month', 'Report period cannot exceed 36 months.');
                    }
                }
            },
        ];
    }
}
