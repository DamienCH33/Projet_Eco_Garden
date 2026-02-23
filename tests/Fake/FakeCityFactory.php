<?php

namespace App\Tests\Fake;

use App\Entity\City;
use App\Factory\CityFactory;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FakeCityFactory extends CityFactory
{
    private City $city;

    public function __construct(City $city)
    {
        $this->city = $city;
    }

    public function findOrCreateFromPostalCode(
        string $postalCode,
        HttpClientInterface $client,
        string $openweatherApiKey,
    ): City {
        return $this->city;
    }
}
