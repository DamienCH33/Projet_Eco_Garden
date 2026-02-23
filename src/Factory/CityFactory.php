<?php

namespace App\Factory;

use App\Entity\City;
use App\Repository\CityRepository;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CityFactory
{
    public function __construct(
        private CityRepository $cityRepository,
    ) {
    }

    /**
     * Trouve ou crée une ville depuis un code postal.
     */
    public function findOrCreateFromPostalCode(
        string $postalCode,
        HttpClientInterface $client,
        string $apiKey,
    ): City {
        $city = $this->cityRepository->findOneBy([
            'postalCode' => $postalCode,
        ]);

        if ($city) {
            return $city;
        }

        $response = $client->request(
            'GET',
            'https://api.openweathermap.org/geo/1.0/zip',
            [
                'query' => [
                    'zip' => $postalCode.',FR',
                    'appid' => $apiKey,
                ],
            ]
        );

        $result = $response->toArray();

        if (empty($result['name']) || empty($result['country'])) {
            throw new \Exception('Impossible de récupérer la ville depuis le code postal');
        }

        $city = new City();
        $city->setPostalCode($postalCode);
        $city->setName($result['name']);
        $city->setCountry($result['country']);

        return $city;
    }
}
