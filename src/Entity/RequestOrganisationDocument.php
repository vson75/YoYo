<?php

namespace App\Entity;

use App\Repository\RequestOrganisationDocumentRepository;
use App\Service\UploadService;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RequestOrganisationDocumentRepository::class)
 */
class RequestOrganisationDocument
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
    private $filename;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $originalFilename;


    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $DepositeDate;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $mimeType;

    /**
     * @ORM\ManyToOne(targetEntity=RequestStatus::class, inversedBy="requestOrganisationDocuments")
     */
    private $RequestStatus;

    /**
     * @ORM\ManyToOne(targetEntity=DocumentType::class, inversedBy="requestOrganisationDocuments")
     */
    private $DocumentType;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="requestOrganisationDocuments")
     */
    private $user;

    public function getId(): ?int
    {
        return $this->id;
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

    public function setOriginalFilename(?string $originalFilename): self
    {
        $this->originalFilename = $originalFilename;

        return $this;
    }

    public function getDepositeDate(): ?\DateTimeInterface
    {
        return $this->DepositeDate;
    }

    public function setDepositeDate(?\DateTimeInterface $DepositeDate): self
    {
        $this->DepositeDate = $DepositeDate;

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

    public function getRequestStatus(): ?RequestStatus
    {
        return $this->RequestStatus;
    }

    public function setRequestStatus(?RequestStatus $RequestStatus): self
    {
        $this->RequestStatus = $RequestStatus;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }


    public function getDocumentPath(): string
    {
        return UploadService::Organisation_document_path.$this->getUser()->getId().'/'.$this->getFilename();
    }

    public function getUploadsDownloadDocumentPath(): string
    {
        return UploadService::Organisation_document_Upload_Download_Path.$this->getUser()->getId().'/'.$this->getFilename();
    }
}
