<?php

namespace App\Services\State\Provider\DiverCity;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\DiverCity\SpaceAvailability;
use App\Repository\DiverCity\BlockedPeriodRepository;
use App\Repository\DiverCity\BookingRepository;
use App\Repository\DiverCity\SpaceRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Uid\Uuid;

/**
 * Calcule les indisponibilités d'un espace (réservations acceptées +
 * périodes bloquées) sur une plage de dates, pour l'endpoint :
 * GET /divercity/spaces/{spaceId}/availability?date_from=...&date_to=...
 *
 * Répond à l'exigence de l'étude fonctionnelle §4 : l'utilisateur visualise
 * les dates et créneaux disponibles avant toute demande de réservation.
 */
class SpaceAvailabilityProvider implements ProviderInterface
{
    public function __construct(
        private SpaceRepository $spaceRepository,
        private BookingRepository $bookingRepository,
        private BlockedPeriodRepository $blockedPeriodRepository,
        private RequestStack $requestStack,
    ) {
    }

    /**
     * @return SpaceAvailability[]
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        error_log('SpaceAvailabilityProvider::provide appelé avec spaceId='.($uriVariables['spaceId'] ?? 'ABSENT'));

        $space = $this->spaceRepository->find(Uuid::fromString($uriVariables['spaceId']));
        if (null === $space) {
            throw new NotFoundHttpException('Espace introuvable.');
        }

        $request = $this->requestStack->getCurrentRequest();
        $dateFrom = $this->parseDate($request?->query->get('date_from'), new \DateTime());
        $dateTo = $this->parseDate($request?->query->get('date_to'), (new \DateTime())->modify('+30 days'));

        $availabilities = [];

        foreach ($this->bookingRepository->findAcceptedBetween($space, $dateFrom, $dateTo) as $booking) {
            $availability = new SpaceAvailability();
            $availability->setId('booking-'.$booking->getId());
            $availability->setDate($booking->getDate());
            $availability->setStartTime($booking->getStartTime());
            $availability->setEndTime($booking->getEndTime());
            $availability->setType(SpaceAvailability::TYPE_BOOKING);
            $availability->setTitle($booking->getTitle());
            $availability->setEventActivityTypeLabel($booking->getEventActivityType()?->getLabel());
            $availability->setEventActivityTypeShortLabel($booking->getEventActivityType()?->getShortLabel());
            $availability->setEventActivityTypeColor($booking->getEventActivityType()?->getColor());
            $availabilities[] = $availability;
        }

        foreach ($this->blockedPeriodRepository->findBlockedBetween($space, $dateFrom, $dateTo) as $blockedPeriod) {
            $availability = new SpaceAvailability();
            $availability->setId('blocked-'.$blockedPeriod->getId());
            $availability->setDate($blockedPeriod->getDate());
            $availability->setStartTime($blockedPeriod->getStartTime());
            $availability->setEndTime($blockedPeriod->getEndTime());
            $availability->setType(SpaceAvailability::TYPE_BLOCKED_PERIOD);
            $availability->setTitle($blockedPeriod->getReason());
            $availabilities[] = $availability;
        }

        return $availabilities;
    }

    private function parseDate(?string $value, \DateTime $default): \DateTime
    {
        if (null === $value) {
            return $default;
        }

        $parsed = \DateTime::createFromFormat('Y-m-d', $value);

        return false !== $parsed ? $parsed : $default;
    }
}
