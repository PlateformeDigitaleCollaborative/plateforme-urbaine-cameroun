<?php

namespace App\ApiResource\DiverCity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\QueryParameter;
use App\Entity\DiverCity\Space;
use App\Services\State\Provider\DiverCity\SpaceAvailabilityProvider;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ApiResource(
    paginationEnabled: false,
    operations: [
        new GetCollection(
            uriTemplate: '/divercity/spaces/{spaceId}/availability',
            uriVariables: [
                'spaceId' => new Link(fromClass: Space::class, identifiers: ['id']),
            ],
            provider: SpaceAvailabilityProvider::class,
            normalizationContext: ['groups' => [self::GROUP_READ]],
            parameters: [
                'date_from' => new QueryParameter(),
                'date_to' => new QueryParameter(),
            ],
        ),
    ]
)]
class SpaceAvailability
{
    public const GROUP_READ = 'divercity_space_availability:read';

    public const TYPE_BOOKING = 'booking';
    public const TYPE_BLOCKED_PERIOD = 'blocked_period';

    #[ApiProperty(identifier: true)]
    #[Groups([self::GROUP_READ])]
    private string $id;

    private \DateTimeInterface $date;

    private \DateTimeInterface $startTime;

    private \DateTimeInterface $endTime;

    #[Groups([self::GROUP_READ])]
    private string $type;

    /**
     * Titre de la réservation (type=booking) ou motif de la période bloquée
     * (type=blocked_period). Nullable : le motif d'une période bloquée est
     * optionnel côté admin.
     */
    #[Groups([self::GROUP_READ])]
    private ?string $title = null;

    #[Groups([self::GROUP_READ])]
    private ?string $eventActivityTypeLabel = null;

    #[Groups([self::GROUP_READ])]
    private ?string $eventActivityTypeShortLabel = null;

    #[Groups([self::GROUP_READ])]
    private ?string $eventActivityTypeColor = null;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    #[Groups([self::GROUP_READ])]
    #[SerializedName('date')]
    public function getDateFormat(): ?string
    {
        return $this->date?->format('Y-m-d');
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getStartTime(): \DateTimeInterface
    {
        return $this->startTime;
    }

    #[Groups([self::GROUP_READ])]
    #[SerializedName('startTime')]
    public function getStartTimeFormat(): ?string
    {
        return $this->startTime?->format('H:i');
    }

    public function setStartTime(\DateTimeInterface $startTime): self
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): \DateTimeInterface
    {
        return $this->endTime;
    }

    #[Groups([self::GROUP_READ])]
    #[SerializedName('endTime')]
    public function getEndTimeFormat(): ?string
    {
        return $this->endTime?->format('H:i');
    }

    public function setEndTime(\DateTimeInterface $endTime): self
    {
        $this->endTime = $endTime;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getEventActivityTypeLabel(): ?string
    {
        return $this->eventActivityTypeLabel;
    }

    public function setEventActivityTypeLabel(?string $eventActivityTypeLabel): self
    {
        $this->eventActivityTypeLabel = $eventActivityTypeLabel;

        return $this;
    }

    public function getEventActivityTypeShortLabel(): ?string
    {
        return $this->eventActivityTypeShortLabel;
    }

    public function setEventActivityTypeShortLabel(?string $eventActivityTypeShortLabel): self
    {
        $this->eventActivityTypeShortLabel = $eventActivityTypeShortLabel;

        return $this;
    }

    public function getEventActivityTypeColor(): ?string
    {
        return $this->eventActivityTypeColor;
    }

    public function setEventActivityTypeColor(?string $eventActivityTypeColor): self
    {
        $this->eventActivityTypeColor = $eventActivityTypeColor;

        return $this;
    }
}
