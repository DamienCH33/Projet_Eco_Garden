<?php

namespace App\Factory;

use App\Entity\Advice;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class AdviceFactory
{
    private PropertyAccessor $accessor;

    public function __construct()
    {
        $this->accessor = new PropertyAccessor();
    }

    /**
     * Création Advice.
     */
    public function createAdviceFromRequest(Request $request): Advice
    {
        $data = json_decode($request->getContent(), true);

        if (!\is_array($data)) {
            throw new BadRequestHttpException('Invalid JSON.');
        }

        if (empty($data['content']) || !\is_string($data['content'])) {
            throw new BadRequestHttpException('Content is required.');
        }

        if (
            !isset($data['month'])
            || !\is_array($data['month'])
            || 0 === \count($data['month'])
        ) {
            throw new BadRequestHttpException('Month is required.');
        }

        foreach ($data['month'] as $month) {
            if ($month < 1 || $month > 12) {
                throw new BadRequestHttpException('Month must be between 1 and 12');
            }
        }

        $advice = new Advice();

        foreach ($data as $key => $value) {
            if ($this->accessor->isWritable($advice, $key)) {
                $this->accessor->setValue($advice, $key, $value);
            }
        }

        return $advice;
    }

    /**
     * Update Advice.
     */
    public function updateAdviceFromRequest(Advice $advice, Request $request): Advice
    {
        $data = json_decode($request->getContent(), true);

        if (!\is_array($data)) {
            throw new BadRequestHttpException('Invalid JSON.');
        }

        if (isset($data['month'])) {
            if (!\is_array($data['month']) || 0 === \count($data['month'])) {
                throw new BadRequestHttpException('Month is required.');
            }

            foreach ($data['month'] as $month) {
                if ($month < 1 || $month > 12) {
                    throw new BadRequestHttpException('Month must be between 1 and 12');
                }
            }
        }

        foreach ($data as $key => $value) {
            if ($this->accessor->isWritable($advice, $key)) {
                $this->accessor->setValue($advice, $key, $value);
            }
        }

        return $advice;
    }
}
