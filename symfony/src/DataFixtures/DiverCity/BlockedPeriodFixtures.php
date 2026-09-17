<?php

namespace App\DataFixtures\DiverCity;

use App\Entity\DiverCity\BlockedPeriod;
use App\Entity\DiverCity\Space;
use App\Entity\User\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Uid\Uuid;

/**
 * Périodes d'indisponibilité de l'espace DiverCity : maintenance, fermeture
 * annuelle, réunions internes récurrentes, plus une période débloquée pour
 * couvrir le cas `isUnblocked`.
 *
 * Les dates sont relatives à la date de chargement afin que le calendrier de
 * disponibilités affiche toujours quelque chose.
 */
class BlockedPeriodFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var Space $space */
        $space = $this->getReference(SpaceFixtures::REFERENCE_MAIN_SPACE, Space::class);

        $userRepository = $manager->getRepository(User::class);
        $admin = $userRepository->findOneBy(['email' => 'admin@test.com']);
        $editor = $userRepository->findOneBy(['email' => 'editor@test.com']);

        // 1. Maintenance technique ponctuelle (journée entière).
        $this->createBlockedPeriod(
            $manager,
            $space,
            '+3 days',
            '08:00',
            '18:00',
            'Maintenance technique de la salle (sonorisation et vidéoprojecteur)',
            $admin,
            createdAtModifier: '-10 days',
        );

        // 2. Nettoyage approfondi en fin de journée.
        $this->createBlockedPeriod(
            $manager,
            $space,
            '+10 days',
            '16:00',
            '19:00',
            'Nettoyage approfondi et réagencement du mobilier',
            $editor,
            createdAtModifier: '-6 days',
        );

        // 3. Fermeture annuelle : 3 jours consécutifs, même groupe de récurrence.
        $closingGroupId = Uuid::v4()->toRfc4122();
        foreach (['+45 days', '+46 days', '+47 days'] as $modifier) {
            $this->createBlockedPeriod(
                $manager,
                $space,
                $modifier,
                '08:00',
                '18:00',
                'Fermeture annuelle du tiers-lieu (inventaire et travaux)',
                $admin,
                recurrenceGroupId: $closingGroupId,
                createdAtModifier: '-15 days',
            );
        }

        // 4. Réunion hebdomadaire de l'équipe DiverCity : 4 occurrences.
        $weeklyGroupId = Uuid::v4()->toRfc4122();
        foreach (['+5 days', '+12 days', '+19 days', '+26 days'] as $modifier) {
            $this->createBlockedPeriod(
                $manager,
                $space,
                $modifier,
                '14:00',
                '17:00',
                "Réunion hebdomadaire de l'équipe DiverCity",
                $editor,
                recurrenceGroupId: $weeklyGroupId,
                createdAtModifier: '-20 days',
            );
        }

        // 5. Créneau finalement rouvert : conservé en base mais débloqué.
        $this->createBlockedPeriod(
            $manager,
            $space,
            '+17 days',
            '09:00',
            '12:00',
            'Mission terrain annulée — créneau rouvert aux réservations',
            $admin,
            isUnblocked: true,
            createdAtModifier: '-4 days',
        );

        $manager->flush();
    }

    private function createBlockedPeriod(
        ObjectManager $manager,
        Space $space,
        string $dateModifier,
        string $startTime,
        string $endTime,
        string $reason,
        ?User $createdBy,
        bool $isUnblocked = false,
        ?string $recurrenceGroupId = null,
        ?string $createdAtModifier = null,
    ): BlockedPeriod {
        $blockedPeriod = new BlockedPeriod();
        $blockedPeriod->setSpace($space);
        $blockedPeriod->setDate(new \DateTime($dateModifier));
        $blockedPeriod->setStartTime(new \DateTime($startTime));
        $blockedPeriod->setEndTime(new \DateTime($endTime));
        $blockedPeriod->setReason($reason);
        $blockedPeriod->setIsUnblocked($isUnblocked);
        $blockedPeriod->setRecurrenceGroupId($recurrenceGroupId);
        $blockedPeriod->setCreatedAt(new \DateTime($createdAtModifier ?? 'now'));

        if (null !== $createdBy) {
            $blockedPeriod->setCreatedBy($createdBy);
        }

        $manager->persist($blockedPeriod);

        return $blockedPeriod;
    }

    public function getDependencies(): array
    {
        return [
            SpaceFixtures::class,
        ];
    }
}