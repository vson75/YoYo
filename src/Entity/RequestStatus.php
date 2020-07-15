<?php

namespace App\Entity;

use App\Repository\RequestStatusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RequestStatusRepository::class)
 */
class RequestStatus
{

    public const Request_Sent = 1;
    public const Request_Information_tobe_completed = 2;
    public const Request_Validated = 3;
    public const Request_Cancelled = 4;


    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $RequestStatus;

    /**
     * @ORM\OneToMany(targetEntity=RequestOrganisationInfo::class, mappedBy="RequestStatus")
     */
    private $requestOrganisationInfos;

    /**
     * @ORM\OneToMany(targetEntity=RequestOrganisationDocument::class, mappedBy="RequestStatus")
     */
    private $requestOrganisationDocuments;

    public function __construct()
    {
        $this->requestOrganisationInfos = new ArrayCollection();
        $this->requestOrganisationDocuments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRequestStatus(): ?string
    {
        return $this->RequestStatus;
    }

    public function setRequestStatus(?string $RequestStatus): self
    {
        $this->RequestStatus = $RequestStatus;

        return $this;
    }

    /**
     * @return Collection|RequestOrganisationInfo[]
     */
    public function getRequestOrganisationInfos(): Collection
    {
        return $this->requestOrganisationInfos;
    }

    public function addRequestOrganisationInfo(RequestOrganisationInfo $requestOrganisationInfo): self
    {
        if (!$this->requestOrganisationInfos->contains($requestOrganisationInfo)) {
            $this->requestOrganisationInfos[] = $requestOrganisationInfo;
            $requestOrganisationInfo->setRequestStatus($this);
        }

        return $this;
    }

    public function removeRequestOrganisationInfo(RequestOrganisationInfo $requestOrganisationInfo): self
    {
        if ($this->requestOrganisationInfos->contains($requestOrganisationInfo)) {
            $this->requestOrganisationInfos->removeElement($requestOrganisationInfo);
            // set the owning side to null (unless already changed)
            if ($requestOrganisationInfo->getRequestStatus() === $this) {
                $requestOrganisationInfo->setRequestStatus(null);
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
            $requestOrganisationDocument->setRequestStatus($this);
        }

        return $this;
    }

    public function removeRequestOrganisationDocument(RequestOrganisationDocument $requestOrganisationDocument): self
    {
        if ($this->requestOrganisationDocuments->contains($requestOrganisationDocument)) {
            $this->requestOrganisationDocuments->removeElement($requestOrganisationDocument);
            // set the owning side to null (unless already changed)
            if ($requestOrganisationDocument->getRequestStatus() === $this) {
                $requestOrganisationDocument->setRequestStatus(null);
            }
        }

        return $this;
    }
}
