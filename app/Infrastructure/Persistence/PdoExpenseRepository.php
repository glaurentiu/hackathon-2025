<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Entity\Expense;
use App\Domain\Entity\User;
use App\Domain\Repository\ExpenseRepositoryInterface;
use DateTimeImmutable;
use Exception;
use PDO;
use Psr\Log\LoggerInterface;

class PdoExpenseRepository implements ExpenseRepositoryInterface
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @throws Exception
     */
    public function find(int $id): ?Expense
    {
        $query = 'SELECT * FROM expenses WHERE id = :id';
        $statement = $this->pdo->prepare($query);
        $statement->execute(['id' => $id]);
        $data = $statement->fetch();
        if (false === $data) {
            return null;
        }

        return $this->createExpenseFromData($data);
    }

    public function save(Expense $expense): void
    {
        // TODO: Implement save() method.
        $query = 'INSERT INTO expenses (user_id, date, category, amount_cents, description)
                VALUES (:user_id,:date,:category,:amount_cents,:description)';
        $statement = $this->pdo->prepare($query);
        try {

            $statement->execute([
                'user_id' => $expense->userId,
                'date' => $expense->date->format('Y-m-d H:i:s'),
                'category' => $expense->category,
                'amount_cents' => $expense->amountCents,
                'description' => $expense->description
            ]);
        } catch (\PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function delete(int $id): void
    {
        $statement = $this->pdo->prepare('DELETE FROM expenses WHERE id=?');
        $statement->execute([$id]);
    }

    public function findBy(array $criteria, int $from, int $limit): array
    {
        // TODO: Implement findBy() method.
        try {

            $query = 'SELECT * FROM expenses WHERE 1=1';
            $params = [];
            if (isset($criteria['user_id'])) {
                $query .= ' AND user_id = ?';
                $params[] = (int) $criteria['user_id'];
            }
            if (isset($criteria['year'])) {
                $query .= ' AND strftime("%Y", date) = ?';
                $params[] = (int) $criteria['year'];
            }
            if (isset($criteria['month'])) {
                $query .= ' AND strftime("%m", date) = ?';
                $params[] = sprintf('%02d', (int) $criteria['month']);
            }


            // Add pagination
            $query .= ' ORDER BY date DESC LIMIT ? OFFSET ?';
            $params[] = (int) $limit;
            $params[] = (int) $from;


            $statement = $this->pdo->prepare($query);
            $statement->execute($params);
            $expenses = [];
            while ($data = $statement->fetch(PDO::FETCH_ASSOC)) {
                $data['date'] = (new DateTimeImmutable($data['date']))->format('Y-m-d');
                $expenses[] = $this->createExpenseFromData($data);
            }

            return $expenses;
        } catch (\PDOException $e) {
            $this->logger->error($e->getMessage());
            return [];
        }
    }


    public function countBy(array $criteria): int
    {
        // TODO: Implement countBy() method.
        return 0;
    }

    public function listExpenditureYears(User $user): array
    {
        // TODO: Implement listExpenditureYears() method.
        return [];
    }

    public function sumAmountsByCategory(array $criteria): array
    {
        // TODO: Implement sumAmountsByCategory() method.
        return [];
    }

    public function averageAmountsByCategory(array $criteria): array
    {
        // TODO: Implement averageAmountsByCategory() method.
        return [];
    }

    public function sumAmounts(array $criteria): float
    {
        // TODO: Implement sumAmounts() method.
        return 0;
    }

    /**
     * @throws Exception
     */
    private function createExpenseFromData(mixed $data): Expense
    {
        return new Expense(
            $data['id'],
            $data['user_id'],
            new DateTimeImmutable($data['date']),
            $data['category'],
            $data['amount_cents'],
            $data['description'],
        );
    }
}
