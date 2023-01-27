<?php

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserDataPersister implements ContextAwareDataPersisterInterface
{
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var UserPasswordHasherInterface
     */
    private $userPasswordHasher;

    public function __construct(UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userRepository = $userRepository;
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof User;
    }

    /**
     * @param User $data
     * @param array $context
     * @return object|void
     */
    public function persist($data, array $context = [])
    {
        $plainPassword = $data->getPlainPassword();
        if ($plainPassword) {
            $data->setPassword($this->userPasswordHasher->hashPassword($data, $plainPassword));
        }
        $data->eraseCredentials();
        $this->userRepository->add($data, true);
    }

    /**
     * @param User $data
     * @param array $context
     * @return void
     */
    public function remove($data, array $context = [])
    {
        $this->userRepository->remove($data);
    }
}