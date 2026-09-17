<?php

namespace App\Entity;

use App\Repository\GeoDataRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: GeoDataRepository::class)]
class GeoData
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'bigint', nullable: true)]
    #[Groups([Project::WRITE, Project::GET_FULL, Resource::GET_FULL, Actor::ACTOR_READ_ITEM, Actor::ACTOR_WRITE, Resource::WRITE])]
    private ?string $osmId = null;

    #[ORM\Column(nullable: true)]
    #[Groups([Project::WRITE, Project::GET_FULL, Resource::GET_FULL, Actor::ACTOR_READ_ITEM, Actor::ACTOR_WRITE, Resource::WRITE])]
    private ?string $osmType = 'node';

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([Project::WRITE, PROJECT::GET_FULL, PROJECT::GET_PARTIAL, Resource::GET_FULL, Resource::WRITE, Actor::ACTOR_READ_COLLECTION, Actor::ACTOR_READ_ITEM, Actor::ACTOR_WRITE])]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    #[Groups([Project::WRITE, Resource::WRITE, Actor::ACTOR_WRITE, Actor::ACTOR_READ_ITEM, Resource::GET_FULL])]
    #[Assert\Range(min: -90, max: 90)]
    private ?float $latitude = null;

    #[ORM\Column(nullable: true)]
    #[Groups([Project::WRITE, Resource::WRITE, Actor::ACTOR_WRITE, Actor::ACTOR_READ_ITEM, Resource::GET_FULL])]
    #[Assert\Range(min: -180, max: 180)]
    private ?float $longitude = null;

    #[ORM\Column(nullable: true)]
    private ?array $bounds = null;

    #[ORM\Column(nullable: true)]
    private ?array $address = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOsmId(): ?string
    {
        return $this->osmId;
    }

    public function setOsmId(?string $osmId): static
    {
        // Typecast to string to avoid Doctrine error on BIGINT
        $this->osmId = null !== $osmId ? (string) $osmId : null;

        return $this;
    }

    public function getOsmIdentifier(): ?string
    {
        if (null === $this->osmId || null === $this->osmType) {
            return null;
        }

        return strtoupper($this->osmType)[0].$this->osmId;
    }

    public function getOsmType(): ?string
    {
        return $this->osmType;
    }

    public function setOsmType(?string $osmType): static
    {
        $this->osmType = $osmType;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getBounds(): ?array
    {
        return $this->bounds;
    }

    public function setBounds(?array $bounds): static
    {
        $this->bounds = $bounds;

        return $this;
    }

    public function getAddress(): ?array
    {
        return $this->address;
    }

    public function setAddress(?array $address): static
    {
        $this->address = $address;

        return $this;
    }

    #[Groups([Project::GET_FULL, Project::GET_PARTIAL, Resource::GET_FULL, Actor::ACTOR_READ_COLLECTION, Actor::ACTOR_READ_ITEM, Actor::ACTOR_READ_COLLECTION_ALL])]
    public function getCoords(): ?array
    {
        return [
            'lat' => $this->latitude,
            'lng' => $this->longitude,
        ];
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }
}
