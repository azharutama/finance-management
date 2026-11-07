<?php

namespace Database\Factories;

use App\Models\ChartOfAccount;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'date' => fake()->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'coa_id' => ChartOfAccount::factory(),
            'description' => fake()->sentence(4),
            'debit' => 100000,
            'credit' => 0,
        ];
    }

    public function income(float $amount = 5000000): static
    {
        return $this->state(fn () => [
            'debit' => 0,
            'credit' => $amount,
        ]);
    }

    public function expense(float $amount = 150000): static
    {
        return $this->state(fn () => [
            'debit' => $amount,
            'credit' => 0,
        ]);
    }
}
