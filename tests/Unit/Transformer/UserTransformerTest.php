<?php

namespace App\Tests\Unit\Transformer;

use App\Entity\User;
use App\Transformer\UserTransformer;
use PHPUnit\Framework\TestCase;

class UserTransformerTest extends TestCase
{
    public function testTransformReturnsExpectedStructure(): void
    {
        $user = new User();
        $user->setEmail('user@test.fr');
        $user->setPostalCode('33100');
        $user->setRoles(['ROLE_ADMIN']);

        $transformer = new UserTransformer();

        $result = $transformer->transform($user);

        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('email', $result);
        $this->assertArrayHasKey('postalCode', $result);
        $this->assertArrayHasKey('roles', $result);

        $this->assertNull($result['id']); // pas persisté donc null
        $this->assertEquals('user@test.fr', $result['email']);
        $this->assertEquals('33100', $result['postalCode']);

        $this->assertContains('ROLE_ADMIN', $result['roles']);
        $this->assertContains('ROLE_USER', $result['roles']);
    }
}
