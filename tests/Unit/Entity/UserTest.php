<?php

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    /**
     * Création utilisateur initialise createdAt.
     */
    public function testUserIsCreatedWithCreatedAt(): void
    {
        $user = new User();

        $this->assertInstanceOf(\DateTimeImmutable::class, $user->getCreatedAt());
    }

    /**
     * Email utilisé comme identifiant.
     */
    public function testUserIdentifierReturnsEmail(): void
    {
        $user = new User();
        $user->setEmail('test@test.fr');

        $this->assertEquals(
            'test@test.fr',
            $user->getUserIdentifier()
        );
    }

    /**
     * ROLE_USER toujours présent.
     */
    public function testUserAlwaysHasRoleUser(): void
    {
        $user = new User();

        $user->setRoles(['ROLE_ADMIN']);

        $roles = $user->getRoles();

        $this->assertContains('ROLE_USER', $roles);
        $this->assertContains('ROLE_ADMIN', $roles);
    }

    /**
     * Setter password fonctionne.
     */
    public function testPasswordSetter(): void
    {
        $user = new User();

        $user->setPassword('hashed-password');

        $this->assertEquals('hashed-password', $user->getPassword());
    }

    /**
     * Postal code setter.
     */
    public function testPostalCodeSetter(): void
    {
        $user = new User();

        $user->setPostalCode('33100');

        $this->assertEquals('33100', $user->getPostalCode());
    }

    /**
     * updateTimestamp met à jour updatedAt.
     */
    public function testUpdateTimestampUpdatesUpdatedAt(): void
    {
        $user = new User();

        $this->assertNull($user->getUpdatedAt());

        $user->updateTimestamp();

        $this->assertInstanceOf(
            \DateTimeImmutable::class,
            $user->getUpdatedAt()
        );
    }
}
