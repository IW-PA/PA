<?php

require_once __DIR__ . '/../src/services/ForecastRepositoryInterface.php';
require_once __DIR__ . '/../src/services/ForecastService.php';
require_once __DIR__ . '/TestCase.php';

class FakeForecastRepository implements ForecastRepositoryInterface
{
    private array $accounts;
    private array $incomes;
    private array $expenses;
    private array $exceptions;

    public function __construct(
        array $accounts = [],
        array $incomes = [],
        array $expenses = [],
        array $exceptions = []
    ) {
        $this->accounts = $accounts;
        $this->incomes = $incomes;
        $this->expenses = $expenses;
        $this->exceptions = $exceptions;
    }

    public function getAccounts(int $userId): array
    {
        return $this->accounts;
    }

    public function getIncomes(int $userId): array
    {
        return $this->incomes;
    }

    public function getExpenses(int $userId): array
    {
        return $this->expenses;
    }

    public function getExceptions(int $userId): array
    {
        return $this->exceptions;
    }
}

class ForecastServiceTest extends TestCase
{
    public function testRecurringFlowsWithMonthlyInterest(): void
    {
        $repository = new FakeForecastRepository(
            [
                [
                    'id' => 1,
                    'name' => 'Courant',
                    'description' => '',
                    'balance' => 1000,
                    'interest_rate' => 12, // 1% monthly
                    'tax_rate' => 0,
                ],
            ],
            [
                [
                    'id' => 1,
                    'account_id' => 1,
                    'name' => 'Salaire',
                    'amount' => 100,
                    'frequency' => 'mensuel',
                    'start_date' => '2025-01-01',
                    'end_date' => null,
                ],
            ],
            [
                [
                    'id' => 1,
                    'account_id' => 1,
                    'name' => 'Abonnement',
                    'amount' => 50,
                    'frequency' => 'mensuel',
                    'start_date' => '2025-01-01',
                    'end_date' => null,
                ],
            ],
        );

        $service = new ForecastService(1, $repository);
        $result = $service->buildForecast(
            new DateTime('2025-03-01'),
            new DateTime('2025-01-01')
        );

        $summary = $result['summary'];

        $this->assertSame(1000.0, $summary['current_balance']);
        $this->assertEqualsWithDelta(1183.32, $summary['projected_balance'], 0.05);
        $this->assertSame(300.0, $summary['total_income']);
        $this->assertSame(150.0, $summary['total_expense']);
        $this->assertEqualsWithDelta(33.32, $summary['interest_earned'], 0.05);
    }

    public function testExceptionsOverrideRecurringAmounts(): void
    {
        $repository = new FakeForecastRepository(
            [
                [
                    'id' => 1,
                    'name' => 'Test',
                    'description' => '',
                    'balance' => 500,
                    'interest_rate' => 0,
                    'tax_rate' => 0,
                ],
            ],
            [],
            [
                [
                    'id' => 1,
                    'account_id' => 1,
                    'name' => 'Dépense',
                    'amount' => 100,
                    'frequency' => 'mensuel',
                    'start_date' => '2025-01-01',
                    'end_date' => null,
                ],
            ],
            [
                [
                    'id' => 1,
                    'income_id' => null,
                    'expense_id' => 1,
                    'amount' => 0,
                    'frequency' => 'ponctuel',
                    'start_date' => '2025-02-01',
                    'end_date' => '2025-02-01',
                ],
            ]
        );

        $service = new ForecastService(1, $repository);
        $result = $service->buildForecast(
            new DateTime('2025-03-01'),
            new DateTime('2025-01-01')
        );

        $account = $result['accounts'][0];
        $timeline = $account['timeline'];

        $this->assertSame(200.0, $account['total_expense']);
        $this->assertEquals(100.0, $timeline['2025-01']['expense']);
        $this->assertEquals(0.0, $timeline['2025-02']['expense']);
        $this->assertEquals(100.0, $timeline['2025-03']['expense']);
    }
}

