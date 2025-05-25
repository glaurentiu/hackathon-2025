<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Domain\Service\ExpenseService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Views\Twig;
use App\Infrastructure\Persistence\PdoUserRepository;




class ExpenseController extends BaseController
{
    private const PAGE_SIZE = 20;

    public function __construct(
        Twig $view,
        private readonly ExpenseService $expenseService,
        private readonly PdoUserRepository $user,
        private readonly LoggerInterface $logger,


    ) {
        parent::__construct($view);
    }

    public function index(Request $request, Response $response): Response
    {
        // TODO: implement this action method to display the expenses page

        // Hints:
        // - use the session to get the current user ID
        // - use the request query parameters to determine the page number and page size
        // - use the expense service to fetch expenses for the current user

        // parse request parameters
        $userId = $_SESSION['user_id']; // TODO: obtain logged-in user ID from session
        $user = $this->user->find($userId);
        $page = (int) ($request->getQueryParams()['page'] ?? 1);
        $pageSize = (int) ($request->getQueryParams()['pageSize'] ?? self::PAGE_SIZE);
        $year = (int) date('Y');
        $month = (int) date('m');

        $expenses = $this->expenseService->list($user, $year, $month, $page, $pageSize);

        //Get the years and sor them
        $years = [];

        foreach ($expenses as $expense) {
            $expenseString = $expense->date->format('Y-m-d H:i:s');
            $years[] = date('Y', strtotime($expenseString));
        }

        $years = array_unique($years);
        rsort($years);

        //Months

        

        return $this->render($response, 'expenses/index.twig', [
            'expenses' => $expenses,
            'page' => $page,
            'pageSize' => $pageSize,
            'total' => count($expenses),
            'years' => $years
        ]);
    }

    public function create(Request $request, Response $response): Response
    {
        // TODO: implement this action method to display the create expense page

        // Hints:
        // - obtain the list of available categories from configuration and pass to the view
        $categories = json_decode($_ENV['EXPENSES_CATEGORIES'], true);



        return $this->render($response, 'expenses/create.twig', ['categories' => $categories]);
    }

    public function store(Request $request, Response $response): Response
    {
        // TODO: implement this action method to create a new expense
        $userId = $_SESSION['user_id'];
        $user = $this->user->find($userId);
        $data = $request->getParsedBody();
        $amount = (float) $data['amount'];
        $description = $data['description'];
        $date = new \DateTimeImmutable($data['date']);
        $category = $data['category'];


        try {
            $this->expenseService->create($user, $amount, $description, $date, $category);

            return $response->withHeader('Location', '/expenses')->withStatus(302);
        } catch (\InvalidArgumentException $e) {
            $this->logger->error($e->getMessage());
            $errors = json_decode($e->getMessage(), true);
            $categories = json_decode($_ENV['EXPENSES_CATEGORIES'], true);
            return $this->render($response, 'expenses/create.twig', [
                'categories' => $categories,
                'errors' => $errors
            ]);
        }


        // Hints:
        // - use the session to get the current user ID
        // - use the expense service to create and persist the expense entity
        // - rerender the "expenses.create" page with included errors in case of failure
        // - redirect to the "expenses.index" page in case of success





    }

    public function edit(Request $request, Response $response, array $routeParams): Response
    {
        // TODO: implement this action method to display the edit expense page

        // Hints:
        // - obtain the list of available categories from configuration and pass to the view
        // - load the expense to be edited by its ID (use route params to get it)
        // - check that the logged-in user is the owner of the edited expense, and fail with 403 if not

        $expense = ['id' => 1];

        return $this->render($response, 'expenses/edit.twig', ['expense' => $expense, 'categories' => []]);
    }

    public function update(Request $request, Response $response, array $routeParams): Response
    {
        // TODO: implement this action method to update an existing expense

        // Hints:
        // - load the expense to be edited by its ID (use route params to get it)
        // - check that the logged-in user is the owner of the edited expense, and fail with 403 if not
        // - get the new values from the request and prepare for update
        // - update the expense entity with the new values
        // - rerender the "expenses.edit" page with included errors in case of failure
        // - redirect to the "expenses.index" page in case of success

        return $response;
    }

    public function destroy(Request $request, Response $response, array $routeParams): Response
    {
        // TODO: implement this action method to delete an existing expense

        // - load the expense to be edited by its ID (use route params to get it)
        // - check that the logged-in user is the owner of the edited expense, and fail with 403 if not
        // - call the repository method to delete the expense
        // - redirect to the "expenses.index" page

        return $response;
    }
}
