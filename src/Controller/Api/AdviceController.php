<?php

namespace App\Controller\Api;

use App\Entity\Advice;
use App\Form\AdviceType;
use App\Factory\AdviceFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/advice')]
final class AdviceController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em, private AdviceFactory $adviceFactory) {}

    #[Route('/{month}', name: 'api_advice_by_month', methods: ['GET'], requirements: ['month' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    /**
     * Affiche les conseils pour un mois donné
     *
     * @param  int $month
     * @return JsonResponse
     */
    public function getAdvicesByMonth(int $month): JsonResponse
    {
        $month = (int) $month;

        if ($month < 1 || $month > 12) {
            return $this->json(['error' => 'Invalid month. Must be between 1 and 12.'], 400);
        }

        $allAdvices = $this->em->getRepository(Advice::class)->findAll();
        $advices = array_filter($allAdvices, fn(Advice $a) => in_array($month, $a->getMonth()));

        if (empty($advices)) {
            return $this->json(['message' => 'No advice found for this month.'], 404);
        }

        $data = array_map(fn(Advice $a) => [
            'id' => $a->getId(),
            'content' => $a->getContent(),
            'month' => $a->getMonth(),
        ], $advices);

        return $this->json($data, 200);
    }

    #[Route('/', name: 'api_advice_current_month', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    /**
     * Affiche les conseils pour le mois en cours
     *
     * @return JsonResponse
     */
    public function getAdvicesForCurrentMonth(): JsonResponse
    {
        $currentMonth = (int) date('n');

        $advices = $this->em->getRepository(Advice::class)->findAll();
        $advices = array_filter($advices, fn($advice) => in_array($currentMonth, $advice->getMonth()));

        if (empty($advices)) {
            return $this->json(
                ['message' => 'No advice found for the current month.'],
                Response::HTTP_NOT_FOUND
            );
        }

        $data = array_map(function (Advice $advice) {
            return [
                'id' => $advice->getId(),
                'content' => $advice->getContent(),
                'month' => $advice->getMonth(),
            ];
        }, $advices);

        return $this->json($data, Response::HTTP_OK);
    }

    #[Route('/', name: 'api_advice_create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    /**
     * Crée un nouveau conseil
     *
     * @param  mixed $request
     * @return JsonResponse
     */
    public function createAdvice(Request $request): JsonResponse
    {
        $advice = $this->adviceFactory->createAdviceFromRequest($request);

        $form = $this->createForm(AdviceType::class, $advice);
        $form->submit($request->toArray(), false);

        if (!$form->isValid()) {
            $errors = array_map(fn($e) => $e->getMessage(), iterator_to_array($form->getErrors(true)));
            return $this->json(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->em->persist($advice);
        $this->em->flush();

        return $this->json([
            'status' => 'success',
            'message' => 'Conseil créé avec succès',
            'advice' => [
                'id' => $advice->getId(),
                'content' => $advice->getContent(),
                'month' => $advice->getMonth(),
            ],
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'api_advice_update', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    /**
     * Met à jour un conseil existant
     *
     * @param  mixed $request
     * @param  mixed $advice
     * @return JsonResponse
     */
    public function updateAdvice(Request $request, Advice $advice): JsonResponse
    {
        $advice = $this->adviceFactory->updateAdviceFromRequest($advice, $request);

        $form = $this->createForm(AdviceType::class, $advice);
        $form->submit($request->toArray(), false);

        if (!$form->isValid()) {
            $errors = array_map(fn($e) => $e->getMessage(), iterator_to_array($form->getErrors(true)));
            return $this->json(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->em->flush();

        return $this->json([
            'status' => 'success',
            'message' => 'Conseil mis à jour avec succès',
            'advice' => [
                'id' => $advice->getId(),
                'content' => $advice->getContent(),
                'month' => $advice->getMonth(),
            ],
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'api_advice_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]    
    /**
     * Supprime un conseil existant
     *
     * @param  mixed $advice
     * @return JsonResponse
     */
    public function deleteAdvice(Advice $advice): JsonResponse
    {
        $this->em->remove($advice);
        $this->em->flush();

        return $this->json(
            ['status' => 'success', 'message' => 'Conseil supprimé avec succès'],
            Response::HTTP_NO_CONTENT
        );
    }
}
