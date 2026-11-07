<?php

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    /**
     * Relasi HasMany ke akun Chart of Account (COA).
     *
     * @return HasMany<ChartOfAccount, $this>
     */
    public function chartOfAccounts(): HasMany
    {
        return $this->hasMany(ChartOfAccount::class, 'category_id');
    }

    /**
     * Relasi HasManyThrough ke transaksi melalui COA.
     *
     * @return HasManyThrough<Transaction, ChartOfAccount, $this>
     */
    public function transactions(): HasManyThrough
    {
        return $this->hasManyThrough(
            Transaction::class,
            ChartOfAccount::class,
            'category_id',
            'coa_id',
            'id',
            'id'
        );
    }
}
