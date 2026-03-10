<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/ForecastRepositoryInterface.php';

class ForecastRepository implements ForecastRepositoryInterface
{
    public function getAccounts(int $userId): array
    {
        return fetchAll(
            "SELECT id, name, description, balance, interest_rate, tax_rate
             FROM accounts
             WHERE user_id = ?
             ORDER BY created_at ASC",
            [$userId]
        );
    }

    public function getIncomes(int $userId): array
    {
        return fetchAll(
            "SELECT id, account_id, name, amount, frequency, start_date, end_date
             FROM incomes
             WHERE user_id = ?
             ORDER BY start_date ASC",
            [$userId]
        );
    }

    public function getExpenses(int $userId): array
    {
        return fetchAll(
            "SELECT id, account_id, name, amount, frequency, start_date, end_date
             FROM expenses
             WHERE user_id = ?
             ORDER BY start_date ASC",
            [$userId]
        );
    }

    public function getExceptions(int $userId): array
    {
        return fetchAll(
            "SELECT id, income_id, expense_id, amount, frequency, start_date, end_date
             FROM exceptions
             WHERE user_id = ?",
            [$userId]
        );
    }
}

