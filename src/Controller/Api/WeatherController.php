<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Response\ApiResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/meteo')]
final class WeatherController extends AbstractController
{
    public function __construct(
        private ApiResponse $apiResponse,
        private HttpClientInterface $httpClient,
        private CacheInterface $cache,
        private string $openweatherApiKey,
    ) {
    }

    /**
     * Météo pour un code postal donné.
     */
    #[Route('/{postalCode}', methods: ['GET'])]
    public function byPostalCode(string $postalCode): JsonResponse
    {
        $cacheKey = 'weather_postalcode_'.$postalCode;

        $data = $this->cache->get($cacheKey, function (ItemInterface $item) use ($postalCode) {
            $item->expiresAfter(600);

            $response = $this->httpClient->request(
                'GET',
                'https://api.openweathermap.org/data/2.5/weather',
                [
                    'query' => [
                        'zip' => $postalCode.',FR',
                        'appid' => $this->openweatherApiKey,
                        'units' => 'metric',
                        'lang' => 'fr',
                    ],
                ]
            );

            $content = $response->toArray();

            if (($content['cod'] ?? 200) !== 200) {
                throw new \Exception($content['message'] ?? 'Code postal introuvable');
            }

            return [
                'postalCode' => $postalCode,
                'ville' => $content['name'] ?? null,
                'temperature' => $content['main']['temp'] ?? null,
                'description' => $content['weather'][0]['description'] ?? '',
                'humidite' => $content['main']['humidity'] ?? null,
                'vent' => $content['wind']['speed'] ?? null,
            ];
        });

        return $this->apiResponse->success(
            $data,
            'Météo récupérée avec succès'
        );
    }

    /**
     * Météo pour le code postal de l’utilisateur connecté.
     */
    #[Route('/', name: 'api_weather_user_postalcode', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function byUserPostalCode(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user->getPostalCode()) {
            return $this->apiResponse->error(
                'Code postal de l’utilisateur non renseigné',
                Response::HTTP_BAD_REQUEST
            );
        }

        return $this->byPostalCode($user->getPostalCode());
    }
}
