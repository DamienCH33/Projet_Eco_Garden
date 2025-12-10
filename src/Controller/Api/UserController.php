<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Factory\UserFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/user')]
class UserController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
        private UserFactory $userFactory,
        private string $openweatherApiKey,
    ) {}

    #[Route('', name: 'api_user_create', methods: ['POST'])]    
    /**
     * crée un nouvel utilisateur
     *
     * @param  mixed $request
     * @param  mixed $client
     * @param  mixed $validator
     * @return JsonResponse
     */
    public function createUser(Request $request, HttpClientInterface $client, ValidatorInterface $validator): JsonResponse
    {
        try {
            $user = $this->userFactory->createUserFromRequest($request, $client, $this->openweatherApiKey);

            $errors = $validator->validate($user);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }

                return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
            }

            $hashedPassword = $this->passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashedPassword);
            $user->setRoles(['ROLE_USER']);

            $this->em->persist($user);
            $this->em->flush();

            return $this->json([
                'status' => 'success',
                'message' => 'Utilisateur créé avec succès',
                'user' => [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'postalCode' => $user->getPostalCode(),
                    'roles' => $user->getRoles(),
                ],
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'api_user_update', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]    
    /**
     * met à jour un utilisateur existant
     *
     * @param  mixed $request
     * @param  mixed $user
     * @param  mixed $validator
     * @param  mixed $client
     * @return JsonResponse
     */
    public function updateUser(Request $request, User $user, ValidatorInterface $validator, HttpClientInterface $client): JsonResponse
    {
        $this->userFactory->updateUserFromRequest($user, $request, $client, $this->openweatherApiKey);

        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }

            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $data = json_decode($request->getContent(), true);
        if (!empty($data['password'])) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashedPassword);
        }

        $user->setUpdateAt(new \DateTimeImmutable());
        $this->em->flush();

        return $this->json([
            'status' => 'success',
            'message' => 'Utilisateur mis à jour avec succès',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'postalCode' => $user->getCity()?->getPostalCode(),
                'ville' => $user->getCity()?->getName(),
                'country' => $user->getCity()?->getCountry(),
                'roles' => $user->getRoles(),
            ],
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'api_user_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]    
    /**
     * supprime un utilisateur existant
     *
     * @param  mixed $user
     * @return JsonResponse
     */
    public function deleteUser(User $user): JsonResponse
    {
        if (!$user) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Utilisateur introuvable.'
            ], Response::HTTP_NOT_FOUND);
        }

        $this->em->remove($user);
        $this->em->flush();

        return $this->json([
            'status' => 'success',
            'message' => 'Utilisateur supprimé avec succès',
        ], Response::HTTP_NO_CONTENT);
    }
}
