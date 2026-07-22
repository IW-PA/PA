<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/ForecastRepositoryInterface.php';
require_once __DIR__ . '/ForecastRepository.php';

class ForecastService
{
    private int $userId;
    private DateTime $startDate;
    private DateTime $targetDate;

    private array $accounts = [];
    private array $incomes = [];
    private array $expenses = [];
    private array $exceptionsByIncome = [];
    private array $exceptionsByExpense = [];

    private ForecastRepositoryInterface $repository;

    public function __construct(int $userId, ?ForecastRepositoryInterface $repository = null)
    {
        $this->userId = $userId;
        $this->repository = $repository ?? new ForecastRepository();
    }

    public function buildForecast(DateTime $targetDate, ?DateTime $startDate = null): array
    {
        $this->targetDate = (clone $targetDate)->modify('first day of this month')->setTime(0, 0, 0);
        $this->startDate = $startDate
            ? (clone $startDate)->modify('first day of this month')->setTime(0, 0, 0)
            : (new DateTime('first day of this month'))->setTime(0, 0, 0);

        if ($this->targetDate < $this->startDate) {
            $this->startDate = clone $this->targetDate;
        }

        $this->loadData();

        if (empty($this->accounts)) {
            return [
                'summary' => [
                    'current_balance' => 0,
                    'projected_balance' => 0,
                    'total_income' => 0,
                    'total_expense' => 0,
                    'interest_earned' => 0,
                ],
                'accounts' => [],
                'chart' => [
                    'labels' => [],
                    'balances' => [],
                ],
            ];
        }

        $monthsMeta = $this->buildMonthsRange();
        $monthKeys = array_keys($monthsMeta);

        $incomeSchedules = $this->buildSchedules($this->incomes, $this->exceptionsByIncome);
        $expenseSchedules = $this->buildSchedules($this->expenses, $this->exceptionsByExpense);

        $accountForecasts = [];
        $summary = [
            'current_balance' => 0,
            'projected_balance' => 0,
            'total_income' => 0,
            'total_expense' => 0,
            'interest_earned' => 0,
        ];
        $chartBalances = array_fill_keys($monthKeys, 0);

        foreach ($this->accounts as $account) {
            $accountId = (int) $account['id'];
            $balance = (float) $account['balance'];
            $startingBalance = $balance;
            $totalIncome = 0;
            $totalExpense = 0;
            $totalInterest = 0;
            $timeline = [];

            foreach ($monthKeys as $monthKey) {
                $income = $incomeSchedules[$accountId][$monthKey] ?? 0.0;
                $expense = $expenseSchedules[$accountId][$monthKey] ?? 0.0;

                $balance += ($income - $expense);
                $interest = $this->calculateMonthlyInterest($balance, (float) $account['interest_rate'], (float) $account['tax_rate']);
                $balance += $interest;

                $timeline[$monthKey] = [
                    'income' => round($income, 2),
                    'expense' => round($expense, 2),
                    'interest' => round($interest, 2),
                    'balance' => round($balance, 2),
                ];

                $totalIncome += $income;
                $totalExpense += $expense;
                $totalInterest += $interest;
                $chartBalances[$monthKey] += $balance;
            }

            $accountForecasts[] = [
                'account' => $account,
                'starting_balance' => round($startingBalance, 2),
                'projected_balance' => round($balance, 2),
                'total_income' => round($totalIncome, 2),
                'total_expense' => round($totalExpense, 2),
                'interest_earned' => round($totalInterest, 2),
                'timeline' => $timeline,
            ];

            $summary['current_balance'] += $startingBalance;
            $summary['projected_balance'] += $balance;
            $summary['total_income'] += $totalIncome;
            $summary['total_expense'] += $totalExpense;
            $summary['interest_earned'] += $totalInterest;
        }

        foreach ($summary as $key => $value) {
            $summary[$key] = round($value, 2);
        }

        return [
            'summary' => $summary,
            'accounts' => $accountForecasts,
            'chart' => [
                'labels' => array_values($monthsMeta),
                'balances' => array_map(fn ($value) => round($value, 2), array_values($chartBalances)),
            ],
        ];
    }

    private function loadData(): void
    {
        $this->accounts = $this->repository->getAccounts($this->userId);
        $this->incomes = $this->repository->getIncomes($this->userId);
        $this->expenses = $this->repository->getExpenses($this->userId);

        $exceptions = $this->repository->getExceptions($this->userId);

        foreach ($exceptions as $exception) {
            if (!empty($exception['income_id'])) {
                $this->exceptionsByIncome[$exception['income_id']][] = $exception;
            }

            if (!empty($exception['expense_id'])) {
                $this->exceptionsByExpense[$exception['expense_id']][] = $exception;
            }
        }
    }

    private function buildMonthsRange(): array
    {
        $months = [];
        $current = clone $this->startDate;

        while ($current <= $this->targetDate) {
            $key = $current->format('Y-m');
            $months[$key] = $current->format('M Y');
            $current->modify('+1 month');
        }

        return $months;
    }

    private function buildSchedules(array $records, array $exceptionsByRecord): array
    {
        $schedules = [];

        foreach ($records as $record) {
            if (empty($record['account_id'])) {
                continue;
            }

            $accountId = (int) $record['account_id'];
            $recordSchedule = $this->buildAmountSchedule($record, $exceptionsByRecord[$record['id']] ?? []);

            foreach ($recordSchedule as $month => $amount) {
                if (!isset($schedules[$accountId])) {
                    $schedules[$accountId] = [];
                }

                $schedules[$accountId][$month] = ($schedules[$accountId][$month] ?? 0) + $amount;
            }
        }

        return $schedules;
    }

    private function buildAmountSchedule(array $record, array $exceptions): array
    {
        $schedule = [];
        $baseOccurrences = $this->buildOccurrenceSchedule(
            $record['start_date'] ?? null,
            $record['end_date'] ?? null,
            $record['frequency'] ?? 'mensuel',
            isset($record['interval_months']) ? (int) $record['interval_months'] : null
        );

        $amount = (float) $record['amount'];
        foreach ($baseOccurrences as $month) {
            $schedule[$month] = $amount;
        }

        foreach ($exceptions as $exception) {
            $exceptionOccurrences = $this->buildOccurrenceSchedule(
                $exception['start_date'] ?? $record['start_date'],
                $exception['end_date'] ?? $record['end_date'],
                $exception['frequency'] ?? $record['frequency'] ?? 'mensuel',
                isset($exception['interval_months'])
                    ? (int) $exception['interval_months']
                    : (isset($record['interval_months']) ? (int) $record['interval_months'] : null)
            );

            foreach ($exceptionOccurrences as $month) {
                // An exception REPLACES the amount of an occurrence that already
                // exists. It must never create a charge in a month where the
                // dépense/revenu does not run - outside its start/end dates, or
                // between two occurrences of a coarser frequency.
                if (array_key_exists($month, $schedule)) {
                    $schedule[$month] = (float) $exception['amount'];
                }
            }
        }

        return $schedule;
    }

    private function buildOccurrenceSchedule(?string $startDate, ?string $endDate, ?string $frequency, ?int $intervalMonths = null): array
    {
        $occurrences = [];

        if (!$startDate) {
            return $occurrences;
        }

        $start = (new DateTime($startDate))->modify('first day of this month');
        $end = $endDate ? (new DateTime($endDate))->modify('first day of this month') : clone $this->targetDate;

        if ($end > $this->targetDate) {
            $end = clone $this->targetDate;
        }

        if ($start > $this->targetDate) {
            return $occurrences;
        }

        $interval = $this->frequencyToMonths($frequency, $intervalMonths);

        if ($interval <= 0) {
            // Ponctuelle: a single échéance on its start month. Routing 0 here also
            // stops the loop below from ever stepping '+0 month' forever.
            if ($start >= $this->startDate && $start <= $this->targetDate) {
                $occurrences[] = $start->format('Y-m');
            }
            return $occurrences;
        }

        $current = clone $start;

        while ($current <= $end) {
            if ($current >= $this->startDate && $current <= $this->targetDate) {
                $occurrences[] = $current->format('Y-m');
            }
            $current->modify(sprintf('+%d month', $interval));
        }

        return $occurrences;
    }

    // Months between two échéances. interval_months is authoritative as soon as it
    // is set; the legacy ENUM is only the fallback for rows written before the
    // column existed. 0 means "ponctuelle".
    private function frequencyToMonths(?string $frequency, ?int $intervalMonths = null): int
    {
        if ($intervalMonths !== null) {
            return max(0, $intervalMonths);
        }

        $normalized = strtolower(trim((string) $frequency));

        switch ($normalized) {
            case 'ponctuel':
                return 0;
            case 'mensuel':
            case 'recurrent': // 'recurrent' with no interval degrades to monthly
                return 1;
            case 'bimensuel':
            case 'tous les 2 mois':
                return 2;
            case 'trimestriel':
            case 'tous les 3 mois':
                return 3;
            case 'semestriel':
            case 'tous les 6 mois':
                return 6;
            case 'annuel':
            case 'tous les 12 mois':
                return 12;
            default:
                return 1;
        }
    }

    private function calculateMonthlyInterest(float $balance, float $annualRate, float $taxRate): float
    {
        if ($annualRate <= 0 || $balance <= 0) {
            return 0.0;
        }

        $monthlyRate = ($annualRate / 100) / 12;
        $grossInterest = $balance * $monthlyRate;
        $netInterest = $grossInterest * (1 - ($taxRate / 100));

        return round($netInterest, 2);
    }
}

