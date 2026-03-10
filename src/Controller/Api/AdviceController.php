<?php

namespace App\Controller\Api;

use App\Entity\Advice;
use App\Factory\AdviceFactory;
use App\Form\AdviceType;
use App\Repository\AdviceRepository;
use App\Response\ApiResponse;
use App\Transformer\AdviceTransformer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/advice')]
final class AdviceController extends AbstractController
{
    public function __construct(private AdviceRepository $adviceRepository, private AdviceFactory $adviceFactory, private ApiResponse $apiResponse, private AdviceTransformer $transformer)
    {
    }

    #[Route('/{month}', name: 'api_advice_by_month', methods: ['GET'], requirements: ['month' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    /**
     * Affiche les conseils pour un mois donné.
     */
    public function getAdvicesByMonth(int $month): JsonResponse
    {
        $month = (int) $month;

        if ($month < 1 || $month > 12) {
            return $this->apiResponse->error(
                'Invalid month. Must be between 1 and 12.',
                Response::HTTP_BAD_REQUEST
            );
        }

        $advices = $this->adviceRepository->findByMonth($month);

        if (empty($advices)) {
            return $this->apiResponse->error(
                'No advice found for this month.',
                Response::HTTP_NOT_FOUND
            );
        }

        $data = $this->transformer->transformCollection($advices);

        return $this->apiResponse->success(
            $data,
            'Advices retrieved successfully'
        );
    }

    #[Route('/', name: 'api_advice_current_month', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    /**
     * Affiche les conseils pour le mois en cours.
     */
    public function getAdvicesForCurrentMonth(): JsonResponse
    {
        $currentMonth = (int) (new \DateTime())->format('n');

        $advices = $this->adviceRepository->findByMonth($currentMonth);

        if (empty($advices)) {
            return $this->apiResponse->error(
                'No advice found for the current month.',
                Response::HTTP_NOT_FOUND
            );
        }

        $data = $this->transformer->transformCollection($advices);

        return $this->apiResponse->success(
            $data,
            'Advices retrieved successfully'
        );
    }

    #[Route('/', name: 'api_advice_create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    /**
     * Crée un nouveau conseil.
     *
     * @param mixed $request
     */
    public function createAdvice(Request $request): JsonResponse
    {
        $advice = $this->adviceFactory->createAdviceFromRequest($request);

        $form = $this->createForm(AdviceType::class, $advice);
        $form->submit($request->toArray(), false);

        if (!$form->isValid()) {
            $errors = array_map(static fn ($e) => $e->getMessage(), iterator_to_array($form->getErrors(true)));

            return $this->apiResponse->error(
                implode(', ', $errors),
                Response::HTTP_BAD_REQUEST
            );
        }

        $this->adviceRepository->save($advice);

        return $this->apiResponse->success(
            $this->transformer->transform($advice),
            'Conseil créé avec succès',
            Response::HTTP_CREATED
        );
    }

    #[Route('/{id}', name: 'api_advice_update', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    /**
     * Met à jour un conseil existant.
     *
     * @param mixed $request
     * @param mixed $advice
     */
    public function updateAdvice(Request $request, Advice $advice): JsonResponse
    {
        $advice = $this->adviceFactory->updateAdviceFromRequest($advice, $request);

        $form = $this->createForm(AdviceType::class, $advice);
        $form->submit($request->toArray(), false);

        if (!$form->isValid()) {
            $errors = array_map(static fn ($e) => $e->getMessage(), iterator_to_array($form->getErrors(true)));

            return $this->apiResponse->error(
                implode(', ', $errors),
                Response::HTTP_BAD_REQUEST
            );
        }

        $this->adviceRepository->save($advice);

        return $this->apiResponse->success(
            $this->transformer->transform($advice),
            'Conseil mis à jour avec succès',
            Response::HTTP_OK
        );
    }

    #[Route('/{id}', name: 'api_advice_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    /**
     * Supprime un conseil existant.
     *
     * @param mixed $advice
     */
    public function deleteAdvice(Advice $advice): JsonResponse
    {
        $this->adviceRepository->remove($advice);

        return $this->apiResponse->success(
            null,
            'Conseil supprimé avec succès'
        );
    }
}
