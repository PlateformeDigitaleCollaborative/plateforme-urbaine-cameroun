<?php

namespace App\DataFixtures\DiverCity;

use App\Entity\DiverCity\Booking;
use App\Entity\DiverCity\HighlightedResource;
use App\Entity\Resource;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Ressources mises "à la une" sur l'espace DiverCity.
 *
 * Seules les ressources déjà liées à une réservation DiverCity sont
 * éligibles (cf. HighlightedResourceProvider) : on part donc des ressources
 * rattachées par BookingFixtures.
 *
 * Les trois premières sont mises en avant (bloc "Actualités à la une"),
 * les suivantes restent éligibles mais non affichées, pour que le panneau
 * d'administration ait des lignes à activer/désactiver.
 */
class HighlightedResourceFixtures extends Fixture implements DependentFixtureInterface
{
    private const HIGHLIGHTED_COUNT = 3;

    public function load(ObjectManager $manager): void
    {
        $resources = $this->collectBookedResources($manager);

        if ([] === $resources) {
            return;
        }

        $position = 0;

        foreach ($resources as $index => $resource) {
            $isHighlighted = $index < self::HIGHLIGHTED_COUNT;

            $highlightedResource = new HighlightedResource();
            $highlightedResource->setResourceId((string) $resource->getId());
            $highlightedResource->setIsHighlighted($isHighlighted);

            if ($isHighlighted) {
                $highlightedResource->setPosition($position);
                $highlightedResource->setHighlightedAt(
                    new \DateTimeImmutable(sprintf('-%d days', 10 - $position))
                );
                ++$position;
            } else {
                $highlightedResource->setPosition(null);
            }

            $manager->persist($highlightedResource);
        }

        $manager->flush();
    }

    /**
     * Ressources rattachées aux réservations, dédoublonnées, dans l'ordre de
     * rattachement.
     *
     * @return Resource[]
     */
    private function collectBookedResources(ObjectManager $manager): array
    {
        $resources = [];

        foreach ($manager->getRepository(Booking::class)->findAll() as $booking) {
            foreach ($booking->getResources() as $resource) {
                $resources[(string) $resource->getId()] = $resource;
            }
        }

        return array_values($resources);
    }

    public function getDependencies(): array
    {
        return [
            BookingFixtures::class,
        ];
    }
}