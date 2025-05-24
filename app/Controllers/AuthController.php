<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Domain\Service\AuthService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Slim\Views\Twig;

class AuthController extends BaseController
{
    public function __construct(
        Twig $view,
        private AuthService $authService,
        private LoggerInterface $logger,
    ) {
        parent::__construct($view);
    }

    public function showRegister(Request $request, Response $response): Response
    {
        // TODO: you also have a logger service that you can inject and use anywhere; file is var/app.log
        $this->logger->info('Register page requested');

        unset($_SESSION['errors']);

        return $this->render($response, 'auth/register.twig');
    }

    public function register(Request $request, Response $response): Response
    {
        // TODO: call corresponding service to perform user registration
        $data = $request->getParsedBody();
        $username = $data['username'];
        $password = $data['password'];
        $errors = [];

        //Validation rules

        if (empty($username)) {
            $errors['username'] = 'Username is required';
        } elseif (strlen(trim($username)) < 4) {
            $errors['username'] = 'Username must be min 4 chars';
        }

        if (empty($password)) {
            $errors['password'] = 'Password is required';
        }elseif (strlen($password) < 9   || !preg_match('/[0-9]/', $password) ) {
            $errors['password'] = 'Password must have at least 8 chars and 1 number';
        }
        try {

            $user = $this->authService->register($username, $password);
            $this->logger->info('User created', ['username' => $username]);

        } catch (\PDOException $e) {
            $this->logger->error("Registration error", [
                'error' => $e->getMessage()
            ]);
            $errors = $_SESSION['errors'] ?? [];
            return $this->render($response, 'auth/register.twig', ['errors' => $errors]);
        }
        ;

        return $response->withHeader('Location', '/login')->withStatus(302);
    }

    public function showLogin(Request $request, Response $response): Response
    {
        return $this->render($response, 'auth/login.twig');
    }

    public function login(Request $request, Response $response): Response
    {
        // TODO: call corresponding service to perform user login, handle login failures
        $data = $request->getParsedBody();
        $username = $data['username'];
        $password = $data['password'];

        $authenticationResult = $this->authService->attempt($username, $password);

        if ($authenticationResult) {
            return $response->withHeader('Location', '/')->withStatus(302);
        }

        $errors['login'] = 'Please check your credentials';
            return $this->render($response, 'auth/login.twig', ['errors' => $errors]);
    }

    public function logout(Request $request, Response $response): Response
    {
        // TODO: handle logout by clearing session data and destroying session
        $_SESSION = [];
        return $response->withHeader('Location', '/login')->withStatus(302);
    }
}
