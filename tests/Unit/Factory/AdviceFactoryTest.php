<?php

namespace App\Tests\Unit\Factory;

use App\Entity\Advice;
use App\Factory\AdviceFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class AdviceFactoryTest extends TestCase
{
    /** Test de la méthode createAdviceFromRequest pour vérifier que
     * le contenu de l'advice est correctement créé à partir de la requête. */
    public function testCreateAdviceFromRequest(): void
    {
        $factory = new AdviceFactory();

        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [],
            json_encode([
                'content' => 'Test factory',
                'month' => [5],
            ])
        );

        $advice = $factory->createAdviceFromRequest($request);

        $this->assertEquals('Test factory', $advice->getContent());
    }

    /**
     * Test de la méthode updateAdviceFromRequest pour vérifier que le contenu de l'advice est mis à jour correctement.
     */
    public function testUpdateAdviceFromRequest(): void
    {
        $factory = new AdviceFactory();

        $advice = new Advice();
        $advice->setContent('Old');
        $advice->setMonth([1]);

        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [],
            json_encode([
                'content' => 'Updated',
            ])
        );

        $updated = $factory->updateAdviceFromRequest($advice, $request);

        $this->assertEquals('Updated', $updated->getContent());
    }
}
