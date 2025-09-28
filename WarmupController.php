<?php
// src/Controller/WarmupController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WarmupController
{
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @Route("/warmup", methods={"GET"})
     */
    public function warmup(Request $request): JsonResponse
    {
        $providedKey = $request->headers->get('X-API-KEY');
        $expectedKey = $_ENV['WARMUP_KEY'] ?? null;

        if ($providedKey !== $expectedKey) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }

        // Endpoints à précharger
        $endpoints = [
            'https://ton-backend-symfony/api/contracts/12345',
            'https://ton-backend-symfony/api/documents/12345',
            'https://ton-backend-symfony/api/avenants/12345'
        ];

        foreach ($endpoints as $url) {
            $this->client->request('GET', $url, ['timeout' => 5]);
        }

        \App\Controller\HealthCheckController::updateLastRequestTime();

        return new JsonResponse(['status' => 'warmup done ✅', 'endpoints' => $endpoints]);
    }
}
