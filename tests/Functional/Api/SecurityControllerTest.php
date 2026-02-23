<?php

namespace App\Tests\Functional\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    /**
     * Route login sans credentials.
     */
    public function testLoginRouteRequiresCredentials(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/auth',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([])
        );

        $this->assertResponseStatusCodeSame(400);
    }

    /**
     * Mauvais credentials.
     */
    public function testLoginWithInvalidCredentials(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/auth',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'fake@test.fr',
                'password' => 'wrong',
            ])
        );

        $this->assertResponseStatusCodeSame(401);
    }
}
