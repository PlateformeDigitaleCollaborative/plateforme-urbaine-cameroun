<?php

namespace App\Services\Service\Geolocation;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Résolution "adresse IP -> pays" pour les KPIs de provenance des connexions.
 *
 * Choix d'implémentation :
 *  - appel à une API publique gratuite et sans clé (ipwho.is par défaut, surchargeable
 *    via la variable d'environnement IP_GEOLOCATION_ENDPOINT) ;
 *  - résultat mis en cache 30 jours par IP, donc en pratique un seul appel réseau par IP
 *    et par mois, ce qui rend le surcoût de latence au login négligeable ;
 *  - tolérance totale aux pannes : si l'API est indisponible, lente ou hors quota, on
 *    renvoie null et la connexion est loguée sans pays. La connexion de l'utilisateur
 *    n'est jamais bloquée par la géolocalisation.
 *
 * Les IP privées / réservées (réseau local, docker, 127.0.0.1) ne sont jamais envoyées
 * à l'extérieur.
 */
class IpGeolocationService
{
    private const CACHE_PREFIX = 'ip_geolocation_';
    private const CACHE_TTL = 2592000; // 30 jours
    private const FAILURE_CACHE_TTL = 3600; // 1 heure (on retente plus vite en cas d'échec)

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly CacheInterface $cache,
        private readonly LoggerInterface $logger,
        private readonly string $ipGeolocationEndpoint = 'https://ipwho.is/{ip}',
        private readonly bool $ipGeolocationEnabled = true,
    ) {
    }

    /**
     * @return array{code: string, name: string}|null
     */
    public function resolve(?string $ip): ?array
    {
        if (!$this->ipGeolocationEnabled || null === $ip || '' === $ip) {
            return null;
        }

        // On ignore les IP privées ou réservées : elles n'ont pas de pays et ne doivent
        // pas être transmises à un service tiers.
        if (false === filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return null;
        }

        try {
            return $this->cache->get(
                self::CACHE_PREFIX.sha1($ip),
                function (ItemInterface $item) use ($ip): ?array {
                    $result = $this->fetchFromProvider($ip);
                    $item->expiresAfter(null === $result ? self::FAILURE_CACHE_TTL : self::CACHE_TTL);

                    return $result;
                }
            );
        } catch (\Throwable $exception) {
            $this->logger->warning('Géolocalisation IP impossible', ['exception' => $exception]);

            return null;
        }
    }

    /**
     * @return array{code: string, name: string}|null
     */
    private function fetchFromProvider(string $ip): ?array
    {
        try {
            $url = str_replace('{ip}', rawurlencode($ip), $this->ipGeolocationEndpoint);

            $payload = $this->httpClient->request('GET', $url, [
                'timeout' => 2,
                'max_duration' => 3,
            ])->toArray(false);
        } catch (\Throwable $exception) {
            $this->logger->warning('Appel au service de géolocalisation IP en échec', [
                'exception' => $exception,
            ]);

            return null;
        }

        // ipwho.is renvoie {"success": false, ...} sur les IP inconnues.
        if (false === ($payload['success'] ?? true)) {
            return null;
        }

        // On accepte les deux conventions de nommage les plus courantes (ipwho.is / ip-api.com)
        // pour rester compatible si l'endpoint est changé via l'environnement.
        $code = $payload['country_code'] ?? $payload['countryCode'] ?? null;
        $name = $payload['country'] ?? $payload['country_name'] ?? null;

        if (!is_string($code) || 2 !== strlen($code)) {
            return null;
        }

        return [
            'code' => strtoupper($code),
            'name' => is_string($name) && '' !== $name ? $name : strtoupper($code),
        ];
    }
}
