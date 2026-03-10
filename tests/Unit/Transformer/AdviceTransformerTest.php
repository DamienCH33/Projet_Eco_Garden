<?php

namespace App\Tests\Unit\Transformer;

use App\Entity\Advice;
use App\Transformer\AdviceTransformer;
use PHPUnit\Framework\TestCase;

class AdviceTransformerTest extends TestCase
{
    public function testTransformCollection(): void
    {
        $advice = new Advice();
        $advice->setContent('Test');
        $advice->setMonth([2]);

        $transformer = new AdviceTransformer();

        $data = $transformer->transformCollection([$advice]);

        $this->assertCount(1, $data);
    }
}
