<?php

namespace App\Models;

use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    /** @use HasFactory<TransactionFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'date',
        'coa_id',
        'description',
        'debit',
        'credit',
    ];

    /**
     * Konfigurasi tipe casting atribut nominal dan tanggal transaksi.
     */
    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
            'coa_id' => 'integer',
            'debit' => 'float',
            'credit' => 'float',
        ];
    }

    /**
     * Relasi BelongsTo ke akun COA transaksi.
     *
     * @return BelongsTo<ChartOfAccount, $this>
     */
    public function coa(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'coa_id');
    }

    /**
     * Alias relasi BelongsTo ke akun COA transaksi.
     *
     * @return BelongsTo<ChartOfAccount, $this>
     */
    public function chartOfAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'coa_id');
    }
}
