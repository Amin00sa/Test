<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TranslateController extends AbstractController
{
    private string $apiKey;
    private string $sourceFile;
    private string $translationsDir;

    public function __construct(string $deeplApiKey)
    {
        $this->apiKey = $deeplApiKey;
        $this->translationsDir = __DIR__ . '/../../translations';
        $this->sourceFile = $this->translationsDir . '/fr.json';
    }

    #[Route('/api/translate', name: 'api_translate')]
    public function translate(Request $request): JsonResponse
    {
        $targetLang = strtoupper($request->query->get('lang', 'DE'));
        $outputFile = "{$this->translationsDir}/{$targetLang}.json";

        // Vérifie si déjà traduit
        if (file_exists($outputFile)) {
            return $this->json(json_decode(file_get_contents($outputFile), true));
        }

        // Charger la langue source
        $sourceJson = json_decode(file_get_contents($this->sourceFile), true);
        if (!$sourceJson) {
            return $this->json(['error' => 'Impossible de lire le fichier source'], 500);
        }

        // Extraire les textes
        $keys = array_keys($sourceJson);
        $values = array_values($sourceJson);

        // Appel à DeepL (batch)
        $params = http_build_query([
            'auth_key' => $this->apiKey,
            'target_lang' => $targetLang,
            'text' => $values
        ]);

        $ch = curl_init('https://api-free.deepl.com/v2/translate');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            return $this->json(['error' => curl_error($ch)], 500);
        }
        curl_close($ch);

        $data = json_decode($response, true);
        if (!isset($data['translations'])) {
            return $this->json(['error' => 'Erreur API DeepL', 'response' => $response], 500);
        }

        // Reconstruire le JSON
        $translated = [];
        foreach ($keys as $i => $key) {
            $translated[$key] = $data['translations'][$i]['text'];
        }

        // Sauvegarder pour la prochaine fois
        file_put_contents($outputFile, json_encode($translated, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return $this->json($translated);
    }
}
