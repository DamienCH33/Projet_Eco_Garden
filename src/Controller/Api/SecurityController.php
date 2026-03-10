<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{
    #[Route('/auth', name: 'api_login', methods: ['POST'])]
    /**
     * login.
     */
    public function login(): JsonResponse
    {
        throw new \LogicException('This should never be reached!');
    }
}
