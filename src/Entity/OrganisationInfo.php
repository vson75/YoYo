<?php

namespace App\Entity;

use App\Repository\OrganisationInfoRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OrganisationInfoRepository::class)
 */
class OrganisationInfo
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $OrganisationName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Address;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $ZipCode;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $City;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $Country;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $PhoneNumber;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrganisationName(): ?string
    {
        return $this->OrganisationName;
    }

    public function setOrganisationName(?string $OrganisationName): self
    {
        $this->OrganisationName = $OrganisationName;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->Address;
    }

    public function setAddress(string $Address): self
    {
        $this->Address = $Address;

        return $this;
    }

    public function getZipCode(): ?string
    {
        return $this->ZipCode;
    }

    public function setZipCode(?string $ZipCode): self
    {
        $this->ZipCode = $ZipCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->City;
    }

    public function setCity(?string $City): self
    {
        $this->City = $City;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->Country;
    }

    public function setCountry(?string $Country): self
    {
        $this->Country = $Country;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->PhoneNumber;
    }

    public function setPhoneNumber(?string $PhoneNumber): self
    {
        $this->PhoneNumber = $PhoneNumber;

        return $this;
    }
}
