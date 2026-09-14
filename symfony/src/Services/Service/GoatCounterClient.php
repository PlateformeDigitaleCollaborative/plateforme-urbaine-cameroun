<?php

namespace App\Services\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Client d'accès à l'API GoatCounter (https://www.goatcounter.com/help/api), utilisé pour
 * exposer les statistiques d'audience (visites uniques, pages consultées) de la PDC sans
 * jamais exposer le token API au frontend.
 *
 * Nécessite deux variables d'environnement :
 * - GOAT_COUNTER_SITE_CODE : le "code" du site GoatCounter (ex: "pdc" pour pdc.goatcounter.com)
 * - GOAT_COUNTER_API_TOKEN : un token API généré depuis le menu utilisateur GoatCounter
 *
 * NB : à vérifier/ajuster une fois les identifiants réels disponibles, la forme exacte des
 * réponses JSON n'a pas pu être testée en conditions réelles dans le cadre de ce développement.
 */
class GoatCounterClient
{
    private const API_VERSION = 'v0';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly ?string $siteCode,
        private readonly ?string $apiToken,
    ) {
    }

    public function isConfigured(): bool
    {
        return !empty($this->siteCode) && !empty($this->apiToken);
    }

    /**
     * Nombre total de vues et de visiteurs uniques sur la période.
     * Cf. GET /api/v0/stats/total.
     */
    public function getTotalStats(\DateTimeInterface $start, \DateTimeInterface $end): array
    {
        return $this->request('/stats/total', [
            'start' => $start->format('Y-m-d'),
            'end' => $end->format('Y-m-d'),
        ]);
    }

    /**
     * Statistiques par page (chemin) sur la période, triées par nombre de vues décroissant.
     * Cf. GET /api/v0/stats/pages.
     */
    public function getPagesStats(\DateTimeInterface $start, \DateTimeInterface $end, int $limit = 100): array
    {
        $response = $this->request('/stats/pages', [
            'start' => $start->format('Y-m-d'),
            'end' => $end->format('Y-m-d'),
            'limit' => $limit,
        ]);

        return $response['pages'] ?? [];
    }

    /**
     * Filtre les statistiques par page pour ne garder que celles dont le chemin
     * commence par $pathPrefix (ex: "/acteurs/" ou "/projets/"), utile pour reconstituer
     * les statistiques de consultation des fiches Acteur/Projet à partir de leur slug.
     */
    public function getPagesStatsForPrefix(\DateTimeInterface $start, \DateTimeInterface $end, string $pathPrefix, int $limit = 200): array
    {
        $pages = $this->getPagesStats($start, $end, $limit);

        return array_values(array_filter(
            $pages,
            static fn (array $page) => isset($page['path']) && str_starts_with($page['path'], $pathPrefix)
        ));
    }

    private function request(string $endpoint, array $query = []): array
    {
        if (!$this->isConfigured()) {
            throw new \RuntimeException('GoatCounter client is not configured (missing GOAT_COUNTER_SITE_CODE or GOAT_COUNTER_API_TOKEN).');
        }

        $url = sprintf('https://%s.goatcounter.com/api/%s%s', $this->siteCode, self::API_VERSION, $endpoint);

        $response = $this->httpClient->request('GET', $url, [
            'headers' => [
                'Authorization' => 'Bearer '.$this->apiToken,
                'Content-Type' => 'application/json',
            ],
            'query' => $query,
        ]);

        return $response->toArray();
    }
}
