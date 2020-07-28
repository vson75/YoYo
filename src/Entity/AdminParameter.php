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
    private $managementFees;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $fixedFees;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getManagementFees(): ?float
    {
        return $this->managementFees;
    }

    public function setManagementFees(?float $managementFees): self
    {
        $this->managementFees = $managementFees;

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
}
