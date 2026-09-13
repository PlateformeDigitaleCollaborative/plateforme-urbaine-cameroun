<?php

namespace App\Entity\DiverCity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\DiverCity\EventActivityTypeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: EventActivityTypeRepository::class)]
#[ORM\Table(name: 'event_activity_type', schema: 'divercity')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(security: 'is_granted("ROLE_ADMIN")'),
        new Put(security: 'is_granted("ROLE_ADMIN")'),
        new Patch(security: 'is_granted("ROLE_ADMIN")'),
        new Delete(security: 'is_granted("ROLE_ADMIN")'),
    ],
    normalizationContext: ['groups' => [self::GROUP_READ]],
)]
class EventActivityType
{
    public const GROUP_READ = 'divercity_event_activity_type:read';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([self::GROUP_READ, Booking::GROUP_READ])]
    private ?int $id = null;

    #[ORM\Column(length: 150, unique: true)]
    #[Groups([self::GROUP_READ, Booking::GROUP_READ, Booking::GROUP_PUBLIC])]
    private ?string $label = null;

    // Libellé court utilisé partout où l'espace est réduit (select, badges,
    // cartes de réservation) — le "label" complet reste réservé aux vues
    // détaillées.
    #[ORM\Column(length: 50, nullable: true)]
    #[Groups([self::GROUP_READ, Booking::GROUP_READ, Booking::GROUP_PUBLIC])]
    private ?string $shortLabel = null;

    #[ORM\Column(length: 7, options: ['default' => '#3B82F6'])]
    #[Groups([self::GROUP_READ, Booking::GROUP_READ, Booking::GROUP_PUBLIC])]
    private ?string $color = '#3B82F6';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getShortLabel(): ?string
    {
        return $this->shortLabel;
    }

    public function setShortLabel(?string $shortLabel): static
    {
        $this->shortLabel = $shortLabel;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): static
    {
        $this->color = $color;

        return $this;
    }
}
