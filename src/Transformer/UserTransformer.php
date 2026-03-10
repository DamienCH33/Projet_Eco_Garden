<?php

namespace App\Transformer;

use App\Entity\User;

class UserTransformer
{
    /**
     * @return array{
     *     id: int|null,
     *     email: string,
     *     postalCode: string,
     *     roles: string[]
     * }
     */
    public function transform(User $user): array
    {
        return [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'postalCode' => $user->getPostalCode(),
            'roles' => $user->getRoles(),
        ];
    }
}
