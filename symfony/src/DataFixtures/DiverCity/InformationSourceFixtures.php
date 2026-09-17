<?php

namespace App\DataFixtures\DiverCity;

use App\Entity\DiverCity\InformationSource;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class InformationSourceFixtures extends Fixture
{
    public const SOURCES = [
        'Bouche-à-oreille',
        'Réseau des acteurs de la plateforme Urbaine',
        'Recommandation',
        'Réseaux sociaux',
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::SOURCES as $label) {
            $informationSource = new InformationSource();
            $informationSource->setLabel($label);
            $manager->persist($informationSource);
        }

        $manager->flush();
    }
}
