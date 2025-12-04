<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;  
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{
    #[Route('/auth', name: 'api_login', methods: ['POST'])]    
    /**
     * login
     *
     * @return JsonResponse
     */
    public function login(): JsonResponse
    {
        // This code is never executed.
        throw new \Exception('This should never be reached!');
    }
}