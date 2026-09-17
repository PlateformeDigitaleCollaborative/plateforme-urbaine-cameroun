<?php

namespace App\DataFixtures\DiverCity;

use App\Entity\DiverCity\EventActivityType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EventActivityTypeFixtures extends Fixture
{
    public const TYPES = [
        [
            'label' => 'Atelier participatif / Co-création',
            'shortLabel' => 'Atelier participatif',
            'color' => '#3B82F6',
        ],
        [
            'label' => 'Conférence / Débat / Table ronde',
            'shortLabel' => 'Conférence / Débat',
            'color' => '#8B5CF6',
        ],
        [
            'label' => 'Formation / Renforcement de capacités',
            'shortLabel' => 'Formation',
            'color' => '#10B981',
        ],
        [
            'label' => "Séminaire / Colloque / Journée d'étude",
            'shortLabel' => 'Séminaire / Colloque',
            'color' => '#F59E0B',
        ],
        [
            'label' => 'Incubation / Accélération de projets',
            'shortLabel' => 'Incubation',
            'color' => '#EC4899',
        ],
        [
            'label' => "Hackathon / Marathon d'innovation",
            'shortLabel' => 'Hackathon',
            'color' => '#EF4444',
        ],
        [
            'label' => 'Événement de réseautage / Partenariat',
            'shortLabel' => 'Réseautage',
            'color' => '#06B6D4',
        ],
        [
            'label' => 'Autres',
            'shortLabel' => 'Autres',
            'color' => '#6B7280',
        ],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::TYPES as $type) {
            $eventActivityType = new EventActivityType();
            $eventActivityType->setLabel($type['label']);
            $eventActivityType->setShortLabel($type['shortLabel']);
            $eventActivityType->setColor($type['color']);
            $manager->persist($eventActivityType);
        }

        $manager->flush();
    }
}