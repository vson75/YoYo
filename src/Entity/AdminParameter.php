<?php

namespace App\Entity;

use App\Repository\AdminParameterRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AdminParameterRepository::class)
 */
class AdminParameter
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $variableFees;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $fixedFees;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $address;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $codePostal;

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
    private $CompanyName;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $AppName;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $capitalSocial;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $sirenNumber;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $webHost;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $phoneNumber;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVariableFees(): ?float
    {
        return $this->variableFees;
    }

    public function setVariableFees(?float $variableFees): self
    {
        $this->variableFees = $variableFees;

        return $this;
    }

    public function getFixedFees(): ?float
    {
        return $this->fixedFees;
    }

    public function setFixedFees(?float $fixedFees): self
    {
        $this->fixedFees = $fixedFees;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getCodePostal(): ?string
    {
        return $this->codePostal;
    }

    public function setCodePostal(?string $codePostal): self
    {
        $this->codePostal = $codePostal;

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

    public function getCompanyName(): ?string
    {
        return $this->CompanyName;
    }

    public function setCompanyName(?string $CompanyName): self
    {
        $this->CompanyName = $CompanyName;

        return $this;
    }

    public function getAppName(): ?string
    {
        return $this->AppName;
    }

    public function setAppName(?string $AppName): self
    {
        $this->AppName = $AppName;

        return $this;
    }

    public function getCapitalSocial(): ?int
    {
        return $this->capitalSocial;
    }

    public function setCapitalSocial(?int $capitalSocial): self
    {
        $this->capitalSocial = $capitalSocial;

        return $this;
    }

    public function getSirenNumber(): ?string
    {
        return $this->sirenNumber;
    }

    public function setSirenNumber(?string $sirenNumber): self
    {
        $this->sirenNumber = $sirenNumber;

        return $this;
    }

    public function getWebHost(): ?string
    {
        return $this->webHost;
    }

    public function setWebHost(?string $webHost): self
    {
        $this->webHost = $webHost;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }


}
