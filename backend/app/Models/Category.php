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

    public const TYPE_INCOME = 'income';

    public const TYPE_EXPENSE = 'expense';

    protected $fillable = [
        'name',
        'type',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<ChartOfAccount, $this>
     */
    public function chartOfAccounts(): HasMany
    {
        return $this->hasMany(ChartOfAccount::class, 'category_id');
    }

    /**
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
