<?php

namespace App\Transformer;

use App\Entity\Advice;

class AdviceTransformer
{
    /**
     * @return array{
     *     id: int|null,
     *     content: string,
     *     month: int[]
     * }
     */
    public function transform(Advice $advice): array
    {
        return [
            'id' => $advice->getId(),
            'content' => $advice->getContent(),
            'month' => $advice->getMonth(),
        ];
    }

    /**
     * @param Advice[] $advices
     *
     * @return array<int, array{
     *     id: int|null,
     *     content: string,
     *     month: int[]
     * }>
     */
    public function transformCollection(array $advices): array
    {
        return array_map([$this, 'transform'], $advices);
    }
}
