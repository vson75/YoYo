<?php

namespace App\Entity;

use App\Repository\UserDocumentRepository;
use App\Service\UploadService;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserDocumentRepository::class)
 */
class UserDocument
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="userDocuments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $filename;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $originalFilename;

    /**
     * @ORM\ManyToOne(targetEntity=DocumentType::class, inversedBy="userDocuments")
     * @ORM\JoinColumn(nullable=false)
     *
     */
    private $DocumentType;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $depositDate;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $mimeType;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(?string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getOriginalFilename(): ?string
    {
        return $this->originalFilename;
    }

    public function setOriginalFilename(string $originalFilename): self
    {
        $this->originalFilename = $originalFilename;

        return $this;
    }

    public function getDocumentType(): ?DocumentType
    {
        return $this->DocumentType;
    }

    public function setDocumentType(?DocumentType $DocumentType): self
    {
        $this->DocumentType = $DocumentType;

        return $this;
    }

    public function getDepositDate(): ?\DateTimeInterface
    {
        return $this->depositDate;
    }

    public function setDepositDate(?\DateTimeInterface $depositDate): self
    {
        $this->depositDate = $depositDate;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(?string $mimeType): self
    {
        $this->mimeType = $mimeType;

        return $this;
    }


    public function getDocumentUserPath(): string
    {
        return UploadService::User_document.$this->getUser()->getId().'/'.$this->getFilename();
    }
}
