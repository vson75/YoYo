<?php

namespace App\Entity;

use App\Repository\OrganisationDocumentRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OrganisationDocumentRepository::class)
 */
class OrganisationDocument
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="organisationDocuments")
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $filename;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $original_filename;

    /**
     * @ORM\ManyToOne(targetEntity=DocumentType::class, inversedBy="organisationDocuments")
     */
    private $documentTypeId;

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
        return $this->original_filename;
    }

    public function setOriginalFilename(?string $original_filename): self
    {
        $this->original_filename = $original_filename;

        return $this;
    }

    public function getDocumentTypeId(): ?DocumentType
    {
        return $this->documentTypeId;
    }

    public function setDocumentTypeId(?DocumentType $documentTypeId): self
    {
        $this->documentTypeId = $documentTypeId;

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
}
