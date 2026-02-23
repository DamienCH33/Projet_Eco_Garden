<?php

namespace App\Tests\Unit\Factory;

use App\Entity\City;
use App\Entity\User;
use App\Factory\UserFactory;
use App\Tests\Fake\FakeCityFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class UserFactoryTest extends TestCase
{
    private HttpClientInterface $httpClient;

    protected function setUp(): void
    {
        $this->httpClient = $this->createStub(HttpClientInterface::class);
    }

    /** Test de la méthode createUserFromRequest pour vérifier que le contenu de l'utilisateur
     * est correctement créé à partir de la requête. */
    public function testCreateUserFromRequestSuccess(): void
    {
        $city = new City();
        $city->setName('Bordeaux');
        $city->setPostalCode('33100');
        $city->setCountry('FR');

        $factory = new UserFactory(
            new FakeCityFactory($city)
        );

        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [],
            json_encode([
                'email' => 'user@test.fr',
                'password' => 'password123',
                'postalCode' => '33100',
            ])
        );

        $user = $factory->createUserFromRequest(
            $request,
            $this->httpClient,
            'fake-key'
        );

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('user@test.fr', $user->getEmail());
        $this->assertEquals('33100', $user->getPostalCode());
        $this->assertSame($city, $user->getCity());
    }

    /** Test de la méthode createUserFromRequest pour vérifier
     * que l'exception est levée lorsque le code postal est invalide. */
    public function testCreateUserThrowsExceptionWhenPostalCodeInvalid(): void
    {
        $factory = new UserFactory(
            new FakeCityFactory(new City())
        );

        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [],
            json_encode([
                'email' => 'user@test.fr',
                'postalCode' => 'abc',
            ])
        );

        $this->expectException(BadRequestHttpException::class);

        $factory->createUserFromRequest(
            $request,
            $this->httpClient,
            'fake-key'
        );
    }

    /** Test de la méthode updateUserFromRequest pour vérifier que le contenu de l'utilisateur
     * est mis à jour correctement. */
    public function testUpdateUserFromRequest(): void
    {
        $city = new City();
        $city->setName('Paris');
        $city->setPostalCode('75001');
        $city->setCountry('FR');

        $factory = new UserFactory(
            new FakeCityFactory($city)
        );

        $user = new User();
        $user->setEmail('old@test.fr');
        $user->setPostalCode('33100');

        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [],
            json_encode([
                'email' => 'new@test.fr',
                'postalCode' => '75001',
            ])
        );

        $updatedUser = $factory->updateUserFromRequest(
            $user,
            $request,
            $this->httpClient,
            'fake-key'
        );

        $this->assertEquals('new@test.fr', $updatedUser->getEmail());
        $this->assertEquals('75001', $updatedUser->getPostalCode());
        $this->assertSame($city, $updatedUser->getCity());
    }

    /** Test de la méthode createUserFromRequest pour vérifier
     * ue si aucun code postal n'est fourni,
     * la ville de l'utilisateur est nulle. */
    public function testCreateUserWithoutPostalCode(): void
    {
        $factory = new UserFactory(new FakeCityFactory(new City()));

        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [],
            json_encode([
                'email' => 'nopostal@test.fr',
            ])
        );

        $user = $factory->createUserFromRequest(
            $request,
            $this->httpClient,
            'fake-key'
        );

        $this->assertNull($user->getCity());
    }
}
