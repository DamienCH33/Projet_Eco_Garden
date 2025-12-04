<?php

namespace App\Factory;

use App\Entity\User;
use App\Entity\City;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class UserFactory
{
    private PropertyAccessor $accessor;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->accessor = new PropertyAccessor();
        $this->em = $em;
    }
    
    /**
     * createUserFromRequest
     *
     * @param  mixed $request
     * @param  mixed $client
     * @param  mixed $openweatherApiKey
     * @return User
     */
    public function createUserFromRequest(Request $request, HttpClientInterface $client, string $openweatherApiKey): User
    {
        $data = json_decode($request->getContent(), true);
        $user = new User();

        foreach ($data as $key => $value) {
            if ($key !== 'postalCode' && $this->accessor->isWritable($user, $key)) {
                $this->accessor->setValue($user, $key, $value);
            }
        }

        if (!empty($data['postalCode'])) {
            $city = $this->getOrCreateCityFromPostalCode($data['postalCode'], $client, $openweatherApiKey);
            $user->setCity($city);
            $user->setPostalCode($data['postalCode']);
        }

        return $user;
    }
    
    /**
     * updateUserFromRequest
     *
     * @param  mixed $user
     * @param  mixed $request
     * @param  mixed $client
     * @param  mixed $openweatherApiKey
     * @return User
     */
    public function updateUserFromRequest(User $user, Request $request, HttpClientInterface $client, string $openweatherApiKey): User
    {
        $data = json_decode($request->getContent(), true);

        if (!empty($data['postalCode'])) {
            $existingCity = $this->em->getRepository(City::class)
                ->findOneBy(['postalCode' => $data['postalCode']]);

            if ($existingCity) {
                $city = $existingCity;
            } else {
                $response = $client->request('GET', 'https://api.openweathermap.org/geo/1.0/zip', [
                    'query' => [
                        'zip' => $data['postalCode'] . ',FR',
                        'appid' => $openweatherApiKey,
                    ],
                ]);

                $result = $response->toArray();

                if (empty($result['name']) || empty($result['country'])) {
                    throw new \Exception('Impossible de récupérer la ville depuis le code postal');
                }

                $city = $this->em->getRepository(City::class)->findOneBy(['postalCode' => $data['postalCode']]) ?? new City();
                $city->setPostalCode($data['postalCode']);
                $city->setName($result['name']);
                $city->setCountry($result['country']);

                $this->em->persist($city);
                $this->em->flush();
            }

            $user->setCity($city);
            $user->setPostalCode($data['postalCode']);
        }

        foreach ($data as $key => $value) {
            if ($key === 'postalCode') continue;
            if ($this->accessor->isWritable($user, $key)) {
                $this->accessor->setValue($user, $key, $value);
            }
        }

        return $user;
    }    
    /**
     * getOrCreateCityFromPostalCode
     *
     * @param  mixed $postalCode
     * @param  mixed $client
     * @param  mixed $openweatherApiKey
     * @return City
     */
    private function getOrCreateCityFromPostalCode(string $postalCode, HttpClientInterface $client, string $openweatherApiKey): City
    {
        $city = $this->em->getRepository(City::class)->findOneBy(['postalCode' => $postalCode]);

        if ($city) {
            return $city;
        }

        $response = $client->request('GET', 'https://api.openweathermap.org/geo/1.0/zip', [
            'query' => [
                'zip' => $postalCode . ',FR',
                'appid' => $openweatherApiKey,
            ],
        ]);

        $result = $response->toArray();

        if (empty($result['name']) || empty($result['country'])) {
            throw new \Exception('Impossible de récupérer la ville depuis le code postal');
        }

        $city = new City();
        $city->setPostalCode($postalCode);
        $city->setName($result['name']);
        $city->setCountry($result['country']);

        $this->em->persist($city);

        return $city;
    }
}
