<?php

namespace App\Factory;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class UserFactory
{
    private PropertyAccessor $accessor;

    public function __construct(
        private CityFactory $cityFactory,
    ) {
        $this->accessor = new PropertyAccessor();
    }

    /**
     * Création utilisateur.
     */
    public function createUserFromRequest(
        Request $request,
        HttpClientInterface $client,
        string $openweatherApiKey,
    ): User {
        $data = json_decode($request->getContent(), true);

        $user = new User();

        foreach ($data as $key => $value) {
            if ('postalCode' !== $key && $this->accessor->isWritable($user, $key)) {
                $this->accessor->setValue($user, $key, $value);
            }
        }

        if (!empty($data['postalCode'])) {
            if (!preg_match('/^\d{5}$/', $data['postalCode'])) {
                throw new BadRequestHttpException('Code postal invalide');
            }

            $city = $this->cityFactory->findOrCreateFromPostalCode(
                $data['postalCode'],
                $client,
                $openweatherApiKey
            );

            $user->setCity($city);
            $user->setPostalCode($data['postalCode']);
        }

        return $user;
    }

    /**
     * Mise à jour utilisateur.
     */
    public function updateUserFromRequest(
        User $user,
        Request $request,
        HttpClientInterface $client,
        string $openweatherApiKey,
    ): User {
        $data = json_decode($request->getContent(), true);

        if (!empty($data['postalCode'])) {
            if (!preg_match('/^\d{5}$/', $data['postalCode'])) {
                throw new BadRequestHttpException('Code postal invalide');
            }

            $city = $this->cityFactory->findOrCreateFromPostalCode(
                $data['postalCode'],
                $client,
                $openweatherApiKey
            );

            $user->setCity($city);
            $user->setPostalCode($data['postalCode']);
        }

        foreach ($data as $key => $value) {
            if ('postalCode' === $key) {
                continue;
            }

            if ($this->accessor->isWritable($user, $key)) {
                $this->accessor->setValue($user, $key, $value);
            }
        }

        return $user;
    }
}
