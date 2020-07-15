<?php

namespace App\Entity;

use App\Repository\DocumentTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DocumentTypeRepository::class)
 */
class DocumentType
{

    public const Identity_card_recto = 1;
    public const Identity_card_verso = 2;
    public const Certificate_organisation = 3;
    public const Bank_account_information = 4;
    public const Awards_justification = 5;



    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $DocumentType;

    /**
     * @ORM\OneToMany(targetEntity=UserDocument::class, mappedBy="DocumentType")
     */
    private $userDocuments;

    /**
     * @ORM\OneToMany(targetEntity=OrganisationDocument::class, mappedBy="documentTypeId")
     */
    private $organisationDocuments;

    /**
     * @ORM\OneToMany(targetEntity=RequestOrganisationDocument::class, mappedBy="DocumentType")
     */
    private $requestOrganisationDocuments;

    public function __construct()
    {
        $this->userDocuments = new ArrayCollection();
        $this->organisationDocuments = new ArrayCollection();
        $this->requestOrganisationDocuments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDocumentType(): ?string
    {
        return $this->DocumentType;
    }

    public function setDocumentType(?string $DocumentType): self
    {
        $this->DocumentType = $DocumentType;

        return $this;
    }

    /**
     * @return Collection|UserDocument[]
     */
    public function getUserDocuments(): Collection
    {
        return $this->userDocuments;
    }

    public function addUserDocument(UserDocument $userDocument): self
    {
        if (!$this->userDocuments->contains($userDocument)) {
            $this->userDocuments[] = $userDocument;
            $userDocument->setDocumentType($this);
        }

        return $this;
    }

    public function removeUserDocument(UserDocument $userDocument): self
    {
        if ($this->userDocuments->contains($userDocument)) {
            $this->userDocuments->removeElement($userDocument);
            // set the owning side to null (unless already changed)
            if ($userDocument->getDocumentType() === $this) {
                $userDocument->setDocumentType(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|OrganisationDocument[]
     */
    public function getOrganisationDocuments(): Collection
    {
        return $this->organisationDocuments;
    }

    public function addOrganisationDocument(OrganisationDocument $organisationDocument): self
    {
        if (!$this->organisationDocuments->contains($organisationDocument)) {
            $this->organisationDocuments[] = $organisationDocument;
            $organisationDocument->setDocumentTypeId($this);
        }

        return $this;
    }

    public function removeOrganisationDocument(OrganisationDocument $organisationDocument): self
    {
        if ($this->organisationDocuments->contains($organisationDocument)) {
            $this->organisationDocuments->removeElement($organisationDocument);
            // set the owning side to null (unless already changed)
            if ($organisationDocument->getDocumentTypeId() === $this) {
                $organisationDocument->setDocumentTypeId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|RequestOrganisationDocument[]
     */
    public function getRequestOrganisationDocuments(): Collection
    {
        return $this->requestOrganisationDocuments;
    }

    public function addRequestOrganisationDocument(RequestOrganisationDocument $requestOrganisationDocument): self
    {
        if (!$this->requestOrganisationDocuments->contains($requestOrganisationDocument)) {
            $this->requestOrganisationDocuments[] = $requestOrganisationDocument;
            $requestOrganisationDocument->setDocumentType($this);
        }

        return $this;
    }

    public function removeRequestOrganisationDocument(RequestOrganisationDocument $requestOrganisationDocument): self
    {
        if ($this->requestOrganisationDocuments->contains($requestOrganisationDocument)) {
            $this->requestOrganisationDocuments->removeElement($requestOrganisationDocument);
            // set the owning side to null (unless already changed)
            if ($requestOrganisationDocument->getDocumentType() === $this) {
                $requestOrganisationDocument->setDocumentType(null);
            }
        }

        return $this;
    }
}
