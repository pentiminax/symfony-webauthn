<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Ulid;
use Webauthn\Bundle\Repository\PublicKeyCredentialUserEntityRepository as PublicKeyCredentialUserEntityRepositoryInterface;
use Webauthn\Exception\InvalidDataException;
use Webauthn\PublicKeyCredentialUserEntity;

final class PublicKeyCredentialUserEntityRepository implements PublicKeyCredentialUserEntityRepositoryInterface
{
    public function __construct(
        private UserRepository $userRepo
    ) {

    }

    public function generateNextUserEntityId(): string {
        return Ulid::generate();
    }

    public function saveUserEntity(PublicKeyCredentialUserEntity $userEntity): void
    {
        $user = $this->userRepo->findOneBy([
            'id' => $userEntity->getId(),
        ]);

        if ($user instanceof UserInterface) {
            return;
        }

        $user = new User($userEntity->getId(), $userEntity->getDisplayName(), $userEntity->getName());

        $this->userRepo->save($user);
    }

    /**
     * @throws InvalidDataException
     */
    public function findOneByUsername(string $username): ?PublicKeyCredentialUserEntity
    {
        $user = $this->userRepo->findOneByDisplayNameOrUserName($username);

        return $this->getUserEntity($user);
    }

    /**
     * @throws InvalidDataException
     */
    public function findOneByUserHandle(string $userHandle): ?PublicKeyCredentialUserEntity
    {
        $user = $this->userRepo->findOneBy([
            'id' => $userHandle,
        ]);

        return $this->getUserEntity($user);
    }

    /**
     * Converts a Symfony User (if any) into a Webauthn User Entity
     * @throws InvalidDataException
     */
    private function getUserEntity(?User $user): ?PublicKeyCredentialUserEntity
    {
        if ($user === null) {
            return null;
        }

        return new PublicKeyCredentialUserEntity($user->getUsername(), $user->getId(), $user->getDisplayName(), null);
    }
}
