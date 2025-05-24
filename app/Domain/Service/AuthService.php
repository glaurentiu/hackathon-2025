<?php

declare(strict_types=1);

namespace App\Domain\Service;

use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;

class AuthService
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly LoggerInterface $logger
    ) {
    }

    public function register(string $username, string $password): User
    {
        // TODO: check that a user with same username does not exist, create new user and persist
        // TODO: make sure password is not stored in plain, and proper PHP functions are used for that
        // TODO: here is a sample code to start with
        $userRegistred = $this->users->findByUsername($username);
        $this->logger->info('User not found');

        if ($userRegistred) {
            $_SESSION['errors']['username'] = 'This user already exists';
        }
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $user = new User(null, $username, $passwordHash, new \DateTimeImmutable());
        $this->users->save($user);
        unset($_SESSION['errors']);
        return $user;
    }

    public function attempt(string $username, string $password): bool
    {
        // TODO: implement this for authenticating the user
        $user = $this->users->findByUsername($username);

        // TODO: make sur ethe user exists and the password matches

        if ($user === null) {
            $this->logger->error("User does not exists");
            return false;
        }


        if (!password_verify($password, $user->passwordHash)) {
            $this->logger->error('Invalid password');
            return false;
        }

        // TODO: don't forget to store in session user data needed afterwards
        $_SESSION['user_id'] = $user->id;
        $_SESSION['username'] = $user->username;

        $this->logger->info('session user id ' . $_SESSION['user_id'] . 'user_id ' . $user->id);


        return true;
    }
}
