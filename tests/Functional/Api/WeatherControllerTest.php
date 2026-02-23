<?php

namespace App\Tests\Functional\Api;

use App\Entity\City;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class WeatherControllerTest extends WebTestCase
{
    /**
     * Créé un utilisateur ROLE_USER et retourne un JWT valide.
     */
    private function getUserToken(KernelBrowser $client): string
    {
        $container = static::getContainer();

        $em = $container->get(EntityManagerInterface::class);
        $hasher = $container->get(UserPasswordHasherInterface::class);

        $email = 'weather_'.uniqid().'@test.fr';

        $city = new City();
        $city->setName('Bordeaux');
        $city->setPostalCode('33100');
        $city->setCountry('FR');

        $user = new User();
        $user->setEmail($email);
        $user->setPostalCode('33100');
        $user->setRoles(['ROLE_USER']);
        $user->setCity($city);
        $user->setPassword(
            $hasher->hashPassword($user, 'password123')
        );

        $em->persist($city);
        $em->persist($user);
        $em->flush();

        $client->request(
            'POST',
            '/auth',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => $email,
                'password' => 'password123',
            ])
        );

        $data = json_decode($client->getResponse()->getContent(), true);

        return $data['token'];
    }

    /**
     * Test météo par code postal.
     */
    public function testWeatherByPostalCode(): void
    {
        $client = static::createClient();

        $token = $this->getUserToken($client);

        $mockResponse = new MockResponse(json_encode([
            'cod' => 200,
            'name' => 'Bordeaux',
            'main' => [
                'temp' => 18,
                'humidity' => 70,
            ],
            'weather' => [
                ['description' => 'ciel dégagé'],
            ],
            'wind' => [
                'speed' => 10,
            ],
        ]));

        $mockHttpClient = new MockHttpClient($mockResponse);

        static::getContainer()->set(
            \Symfony\Contracts\HttpClient\HttpClientInterface::class,
            $mockHttpClient
        );

        $client->request(
            'GET',
            '/meteo/33100',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            ]
        );

        $this->assertResponseIsSuccessful();
    }

    /**
     * Utilisateur sans code postal → 400.
     */
    public function testWeatherUserWithoutPostalCode(): void
    {
        $client = static::createClient();

        $container = static::getContainer();

        $em = $container->get(EntityManagerInterface::class);
        $hasher = $container->get(UserPasswordHasherInterface::class);

        $email = 'nopostal_'.uniqid().'@test.fr';

        $city = new City();
        $city->setName('Paris');
        $city->setPostalCode('75001');
        $city->setCountry('FR');

        $user = new User();
        $user->setEmail($email);
        $user->setPostalCode(''); // volontairement vide
        $user->setRoles(['ROLE_USER']);
        $user->setCity($city);
        $user->setPassword(
            $hasher->hashPassword($user, 'password123')
        );

        $em->persist($city);
        $em->persist($user);
        $em->flush();

        $client->request(
            'POST',
            '/auth',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => $email,
                'password' => 'password123',
            ])
        );

        $token = json_decode($client->getResponse()->getContent(), true)['token'];

        $client->request(
            'GET',
            '/meteo/',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            ]
        );

        $this->assertResponseStatusCodeSame(400);
    }

    /**
     * code postal invalide.
     */
    public function testWeatherInvalidPostalCode(): void
    {
        $client = static::createClient();

        $token = $this->getUserToken($client);

        $mockResponse = new MockResponse(json_encode([
            'cod' => 404,
            'message' => 'city not found',
        ]));

        $mockHttpClient = new MockHttpClient($mockResponse);

        static::getContainer()->set(
            \Symfony\Contracts\HttpClient\HttpClientInterface::class,
            $mockHttpClient
        );

        $client->request(
            'GET',
            '/meteo/00000',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            ]
        );

        $this->assertResponseStatusCodeSame(500);
    }
}
