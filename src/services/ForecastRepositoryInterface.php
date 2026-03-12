<?php

interface ForecastRepositoryInterface
{
    public function getAccounts(int $userId): array;

    public function getIncomes(int $userId): array;

    public function getExpenses(int $userId): array;

    public function getExceptions(int $userId): array;
}

