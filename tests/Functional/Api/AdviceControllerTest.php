<?php

namespace App\Tests\Functional\Api;

use App\Entity\Advice;
use App\Entity\City;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdviceControllerTest extends WebTestCase
{
    private function getUserToken(KernelBrowser $client): string
    {
        $container = static::getContainer();

        $em = $container->get(EntityManagerInterface::class);
        $hasher = $container->get(UserPasswordHasherInterface::class);

        $user = $em->getRepository(User::class)
            ->findOneBy(['email' => 'user@test.fr']);

        if (!$user) {
            $city = new City();
            $city->setName('Bordeaux');
            $city->setPostalCode('33100');
            $city->setCountry('FR');

            $user = new User();
            $user->setEmail('user@test.fr');
            $user->setPostalCode('33100');
            $user->setRoles(['ROLE_USER']);
            $user->setCity($city);
            $user->setPassword(
                $hasher->hashPassword($user, 'password123')
            );

            $em->persist($city);
            $em->persist($user);
            $em->flush();
        }

        $client->request(
            'POST',
            '/auth',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'user@test.fr',
                'password' => 'password123',
            ])
        );

        $data = json_decode($client->getResponse()->getContent(), true);

        if (!isset($data['token'])) {
            throw new \RuntimeException('JWT token not returned');
        }

        return $data['token'];
    }

    private function getAdminToken(KernelBrowser $client): string
    {
        $container = static::getContainer();

        $em = $container->get(EntityManagerInterface::class);
        $hasher = $container->get(UserPasswordHasherInterface::class);

        $admin = $em->getRepository(User::class)
            ->findOneBy(['email' => 'admin@test.fr']);

        if (!$admin) {
            $city = new City();
            $city->setName('Bordeaux');
            $city->setPostalCode('33100');
            $city->setCountry('FR');

            $admin = new User();
            $admin->setEmail('admin@test.fr');
            $admin->setPostalCode('33100');
            $admin->setRoles(['ROLE_ADMIN']);
            $admin->setCity($city);
            $admin->setPassword(
                $hasher->hashPassword($admin, 'password123')
            );

            $em->persist($city);
            $em->persist($admin);
            $em->flush();
        }

        $client->request(
            'POST',
            '/auth',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'admin@test.fr',
                'password' => 'password123',
            ])
        );

        $data = json_decode($client->getResponse()->getContent(), true);

        if (!isset($data['token'])) {
            throw new \RuntimeException('JWT token not returned');
        }

        return $data['token'];
    }

    private function createAdvice(int $month = 1): int
    {
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $advice = new Advice();
        $advice->setContent('Conseil test');
        $advice->setMonth([$month]);

        $em->persist($advice);
        $em->flush();

        return $advice->getId();
    }

    /**
     * accès sans authentification.
     */
    public function testAdviceRequiresAuthentication(): void
    {
        $client = static::createClient();

        $client->request('GET', '/advice/1');

        $this->assertResponseStatusCodeSame(401);
    }

    /**
     * mois invalide.
     */
    public function testGetAdvicesByMonthInvalidMonth(): void
    {
        $client = static::createClient();
        $token = $this->getUserToken($client);

        $client->request(
            'GET',
            '/advice/13',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token]
        );

        $this->assertResponseStatusCodeSame(400);
    }

    /**
     * aucun conseil trouvé.
     */
    public function testGetAdvicesByMonthNotFound(): void
    {
        $client = static::createClient();
        $token = $this->getUserToken($client);

        $client->request(
            'GET',
            '/advice/1',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token]
        );

        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * succès récupération par mois.
     */
    public function testGetAdvicesByMonthSuccess(): void
    {
        $client = static::createClient();
        $token = $this->getUserToken($client);

        $this->createAdvice(12);

        $client->request(
            'GET',
            '/advice/12',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token]
        );

        $this->assertResponseIsSuccessful();
    }

    /**
     * succès mois courant.
     */
    public function testGetCurrentMonthAdvices(): void
    {
        $client = static::createClient();
        $token = $this->getUserToken($client);

        $currentMonth = (int) (new \DateTime())->format('n');
        $this->createAdvice($currentMonth);

        $client->request(
            'GET',
            '/advice/',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token]
        );

        $this->assertResponseIsSuccessful();
    }

    /**
     * création OK.
     */
    public function testCreateAdviceSuccess(): void
    {
        $client = static::createClient();
        $token = $this->getAdminToken($client);

        $client->request(
            'POST',
            '/advice/',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            ],
            json_encode([
                'content' => 'Nouveau conseil',
                'month' => [3],
            ])
        );

        $this->assertResponseStatusCodeSame(201);
    }

    /**
     * validation error → 400.
     */
    public function testCreateAdviceValidationError(): void
    {
        $client = static::createClient();
        $token = $this->getAdminToken($client);

        $client->request(
            'POST',
            '/advice/',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            ],
            json_encode([
                'content' => 'a',
                'month' => [13],
            ])
        );

        $this->assertResponseStatusCodeSame(400);
    }

    /**
     * suppression OK.
     */
    public function testDeleteAdvice(): void
    {
        $client = static::createClient();
        $token = $this->getAdminToken($client);

        $adviceId = $this->createAdvice(5);

        $client->request(
            'DELETE',
            '/advice/'.$adviceId,
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token]
        );

        $this->assertResponseIsSuccessful();
    }

    public function testUpdateAdvice(): void
    {
        $client = static::createClient();
        $token = $this->getAdminToken($client);

        $id = $this->createAdvice(4);

        $client->request(
            'PUT',
            '/advice/'.$id,
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            ],
            json_encode([
                'content' => 'Conseil modifié',
                'month' => [6],
            ])
        );

        $this->assertResponseIsSuccessful();
    }

    /**
     * création interdite pour un utilisateur non admin.
     */
    public function testCreateAdviceForbiddenForUser(): void
    {
        $client = static::createClient();
        $token = $this->getUserToken($client);

        $client->request(
            'POST',
            '/advice/',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            ],
            json_encode([
                'content' => 'test',
                'month' => [1],
            ])
        );

        $this->assertResponseStatusCodeSame(403);
    }

    /**
     * suppression interdite pour un utilisateur non admin.
     */
    public function testDeleteAdviceNotFound(): void
    {
        $client = static::createClient();
        $token = $this->getAdminToken($client);

        $client->request(
            'DELETE',
            '/advice/999999',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$token]
        );

        $this->assertResponseStatusCodeSame(404);
    }

    /** suppression interdite pour un utilisateur non admin.
     */
    public function testUpdateAdviceValidationError(): void
    {
        $client = static::createClient();
        $token = $this->getAdminToken($client);

        $id = $this->createAdvice(2);

        $client->request(
            'PUT',
            '/advice/'.$id,
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            ],
            json_encode([
                'month' => [15],
            ])
        );

        $this->assertResponseStatusCodeSame(400);
    }
}
