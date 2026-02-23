<?php

namespace App\Tests\Functional\Api;

use App\Entity\City;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserControllerTest extends WebTestCase
{
    private function getAdminToken(KernelBrowser $client): string
    {
        $container = static::getContainer();

        $em = $container->get(EntityManagerInterface::class);
        $hasher = $container->get(UserPasswordHasherInterface::class);

        $admin = $em->getRepository(User::class)
            ->findOneBy(['email' => 'admin@test.fr']);

        if (!$admin) {
            $city = $em->getRepository(City::class)
                ->findOneBy(['postalCode' => '33100']);

            if (!$city) {
                $city = new City();
                $city->setName('Bordeaux');
                $city->setPostalCode('33100');
                $city->setCountry('FR');
                $em->persist($city);
            }

            $admin = new User();
            $admin->setEmail('admin@test.fr');
            $admin->setPostalCode('33100');
            $admin->setRoles(['ROLE_ADMIN']);
            $admin->setCity($city);

            $admin->setPassword(
                $hasher->hashPassword($admin, 'password123')
            );

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

        $response = $client->getResponse()->getContent();
        $data = json_decode($response, true);

        if (!isset($data['token'])) {
            throw new \RuntimeException('JWT token not returned. Response: '.$response);
        }

        return $data['token'];
    }

    /**
     * CREATE USER SUCCESS + DB CHECK.
     */
    public function testCreateUserSuccess(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $email = 'test'.uniqid().'@test.fr';

        $client->request(
            'POST',
            '/user',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => $email,
                'password' => 'password123',
                'postalCode' => '33100',
            ])
        );

        $this->assertResponseStatusCodeSame(201);

        $user = $em->getRepository(User::class)
            ->findOneBy(['email' => $email]);

        $this->assertNotNull($user);
    }

    /**
     * VALIDATION ERROR.
     */
    public function testCreateUserValidationError(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/user',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'bad-email',
                'password' => '123',
                'postalCode' => 'abc',
            ])
        );

        $this->assertResponseStatusCodeSame(400);
    }

    /**
     * UPDATE SUCCESS + DB CHECK.
     */
    public function testUpdateUserSuccess(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $token = $this->getAdminToken($client);

        $email = 'update'.uniqid().'@test.fr';

        $client->request(
            'POST',
            '/user',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => $email,
                'password' => 'password123',
                'postalCode' => '33100',
            ])
        );

        $data = json_decode($client->getResponse()->getContent(), true);
        $userId = $data['data']['id'];

        $client->request(
            'PUT',
            '/user/'.$userId,
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            ],
            json_encode([
                'postalCode' => '75001',
            ])
        );

        $this->assertResponseStatusCodeSame(200);

        $updatedUser = $em->getRepository(User::class)->find($userId);

        $this->assertEquals('75001', $updatedUser->getPostalCode());
    }

    /**
     * DELETE SUCCESS + DB CHECK.
     */
    public function testDeleteUserSuccess(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $token = $this->getAdminToken($client);

        $email = 'delete'.uniqid().'@test.fr';

        $client->request(
            'POST',
            '/user',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => $email,
                'password' => 'password123',
                'postalCode' => '33100',
            ])
        );

        $data = json_decode($client->getResponse()->getContent(), true);
        $userId = $data['data']['id'];

        $client->request(
            'DELETE',
            '/user/'.$userId,
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            ]
        );

        $this->assertResponseStatusCodeSame(200);

        $deletedUser = $em->getRepository(User::class)->find($userId);

        $this->assertNull($deletedUser);
    }
}
