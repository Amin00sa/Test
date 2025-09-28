<?php
// src/Controller/HealthCheckController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class HealthCheckController
{
    private const FILE = '/tmp/last_request_time.txt'; // sur Windows, on peut utiliser C:\warmup\last_request_time.txt

    /**
     * @Route("/healthcheck", methods={"GET"})
     */
    public function index(): JsonResponse
    {
        $file = str_replace('/', '\\', 'C:\warmup\last_request_time.txt');

        if (!file_exists($file)) {
            file_put_contents($file, time());
        }

        $lastTime = (int) file_get_contents($file);

        return new JsonResponse([
            'last_request_time' => $lastTime
        ]);
    }

    public static function updateLastRequestTime(): void
    {
        $file = str_replace('/', '\\', 'C:\warmup\last_request_time.txt');
        file_put_contents($file, time());
    }
}
