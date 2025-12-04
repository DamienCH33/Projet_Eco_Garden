<?php

namespace App\Factory;

use App\Entity\Advice;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class AdviceFactory
{
    private PropertyAccessor $accessor;

    public function __construct()
    {
        $this->accessor = new PropertyAccessor();
    }    
    /**
     * createAdviceFromRequest
     *
     * @param  mixed $request
     * @return Advice
     */
    public function createAdviceFromRequest(Request $request): Advice
    {
        $data = json_decode($request->getContent(), true);
        $advice = new Advice();

        foreach ($data as $key => $value) {
            if ($this->accessor->isWritable($advice, $key)) {
                $this->accessor->setValue($advice, $key, $value);
            }
        }

        return $advice;
    }    
    /**
     * updateAdviceFromRequest
     *
     * @param  mixed $advice
     * @param  mixed $request
     * @return Advice
     */
    public function updateAdviceFromRequest(Advice $advice, Request $request): Advice
    {
        $data = json_decode($request->getContent(), true);

        foreach ($data as $key => $value) {
            if ($this->accessor->isWritable($advice, $key)) {
                $this->accessor->setValue($advice, $key, $value);
            }
        }

        return $advice;
    }
}
