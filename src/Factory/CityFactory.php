<?php

namespace App\Factory;

use App\Entity\City;
use App\Repository\CityRepository;

class CityFactory
{
    public function __construct(private CityRepository $cityRepository) {}
    
    /**
     * findOrCreate
     *
     * @param  mixed $cityName
     * @param  mixed $postalCode
     * @return City
     */
    public function findOrCreate(string $cityName, ?string $postalCode = null): City
    {
        $cleanName = ucfirst(strtolower(trim($cityName)));

        $city = $this->cityRepository->findOneBy(['name' => $cleanName]);

        if (!$city) {
            $city = new City();
            $city->setName($cleanName);
            $city->setPostalCode($postalCode);
        }

        return $city;
    }
}
