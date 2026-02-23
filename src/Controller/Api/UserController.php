<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Factory\UserFactory;
use App\Repository\UserRepository;
use App\Response\ApiResponse;
use App\Transformer\UserTransformer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/user')]
class UserController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
        private ApiResponse $apiResponse,
        private UserPasswordHasherInterface $passwordHasher,
        private UserFactory $userFactory,
        private string $openweatherApiKey,
        private UserTransformer $transformer,
    ) {
    }

    #[Route('/me', name: 'api_user_me', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function me(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->apiResponse->success(
            $this->transformer->transform($user),
            'Utilisateur récupéré'
        );
    }

    #[Route('', name: 'api_user_create', methods: ['POST'])]
    public function createUser(
        Request $request,
        HttpClientInterface $client,
        ValidatorInterface $validator,
    ): JsonResponse {
        $user = $this->userFactory->createUserFromRequest(
            $request,
            $client,
            $this->openweatherApiKey
        );

        $errors = $validator->validate($user);

        if (\count($errors) > 0) {
            $messages = array_map(
                static fn ($e) => $e->getMessage(),
                iterator_to_array($errors)
            );

            return $this->apiResponse->error(
                implode(', ', $messages),
                Response::HTTP_BAD_REQUEST
            );
        }

        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $user->getPassword())
        );

        $user->setRoles(['ROLE_USER']);

        $this->userRepository->save($user);

        return $this->apiResponse->success(
            $this->transformer->transform($user),
            'Utilisateur créé avec succès',
            Response::HTTP_CREATED
        );
    }

    #[Route('/{id}', name: 'api_user_update', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    public function updateUser(
        Request $request,
        User $user,
        ValidatorInterface $validator,
        HttpClientInterface $client,
    ): JsonResponse {
        $this->userFactory->updateUserFromRequest(
            $user,
            $request,
            $client,
            $this->openweatherApiKey
        );

        $errors = $validator->validate($user);

        if (\count($errors) > 0) {
            $messages = array_map(
                static fn ($e) => $e->getMessage(),
                iterator_to_array($errors)
            );

            return $this->apiResponse->error(
                implode(', ', $messages),
                Response::HTTP_BAD_REQUEST
            );
        }

        $data = $request->toArray();

        if (!empty($data['password'])) {
            $user->setPassword(
                $this->passwordHasher->hashPassword($user, $user->getPassword())
            );
        }

        $this->userRepository->save($user);

        return $this->apiResponse->success(
            $this->transformer->transform($user),
            'Utilisateur mis à jour avec succès',
            Response::HTTP_OK
        );
    }

    #[Route('/{id}', name: 'api_user_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteUser(User $user): JsonResponse
    {
        $this->userRepository->remove($user);

        return $this->apiResponse->success(
            null,
            'Utilisateur supprimé avec succès'
        );
    }
}
