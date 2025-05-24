<?php

declare(strict_types=1);

namespace App\Domain\Service;

use App\Domain\Entity\Expense;
use App\Domain\Entity\User;
use App\Domain\Repository\ExpenseRepositoryInterface;
use DateTimeImmutable;
use Psr\Http\Message\UploadedFileInterface;

class ExpenseService
{
    public function __construct(
        private readonly ExpenseRepositoryInterface $expenses,
    ) {}

    public function list(User $user, int $year, int $month, int $pageNumber, int $pageSize): array
    {
        // TODO: implement this and call from controller to obtain paginated list of expenses
        
        return [];
    }

    public function create(
        User $user,
        float $amount,
        string $description,
        DateTimeImmutable $date,
        string $category,
    ): void {
        // TODO: implement this to create a new expense entity, perform validation, and persist
        //Validators
        $errors =[];
        if(empty($description)){
            $errors['description']='Description is required';
        }
        if(empty($category)){
            $errors['category']= 'Category must be selected';
        }
        $today = new DateTimeImmutable();
        if($date >= $today){
            $errors['date'] = 'Date must be in the past';
        }

        $amountInCents = (int) ($amount * 100);

        if(!empty($errors)){
            throw new \InvalidArgumentException(json_encode($errors));
        }
        // TODO: here is a code sample to start with
        $expense = new Expense(null, $user->id, $date, $category, $amountInCents, $description);
        $this->expenses->save($expense);
    }

    public function update(
        Expense $expense,
        float $amount,
        string $description,
        DateTimeImmutable $date,
        string $category,
    ): void {
        // TODO: implement this to update expense entity, perform validation, and persist
    }

    public function importFromCsv(User $user, UploadedFileInterface $csvFile): int
    {
        // TODO: process rows in file stream, create and persist entities
        // TODO: for extra points wrap the whole import in a transaction and rollback only in case writing to DB fails

        return 0; // number of imported rows
    }
}
