<?php

namespace App\Entity;

use App\Repository\PostDocumentRepository;
use App\Service\UploadService;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PostDocumentRepository::class)
 */
class PostDocument
{


    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Post::class)
     */
    private $post;

    /**
     * @ORM\ManyToOne(targetEntity=DocumentType::class)
     */
    private $documentType;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $filename;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $originalFilename;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $mimeType;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $depositeDate;

    /**
     * @ORM\OneToOne(targetEntity=PostDateHistoric::class, mappedBy="postDocument", cascade={"persist", "remove"})
     */
    private $postDateHistoric;

    /**
     * @ORM\ManyToOne(targetEntity=EmailContent::class, inversedBy="postDocuments")
     */
    private $EmailContent;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): self
    {
        $this->post = $post;

        return $this;
    }

    public function getDocumentType(): ?DocumentType
    {
        return $this->documentType;
    }

    public function setDocumentType(?DocumentType $documentType): self
    {
        $this->documentType = $documentType;

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

    public function setOriginalFilename(?string $originalFilename): self
    {
        $this->originalFilename = $originalFilename;

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

    public function getDepositeDate(): ?\DateTimeInterface
    {
        return $this->depositeDate;
    }

    public function setDepositeDate(?\DateTimeInterface $depositeDate): self
    {
        $this->depositeDate = $depositeDate;

        return $this;
    }

    public function getProofOfTransfer(){
        return UploadService::Post_Path.$this->getPost()->getId().UploadService::Proof_transfert.$this->getFilename();
    }

    public function getPostDateHistoric(): ?PostDateHistoric
    {
        return $this->postDateHistoric;
    }

    public function setPostDateHistoric(?PostDateHistoric $postDateHistoric): self
    {
        $this->postDateHistoric = $postDateHistoric;

        // set (or unset) the owning side of the relation if necessary
        $newPostDocument = null === $postDateHistoric ? null : $this;
        if ($postDateHistoric->getPostDocument() !== $newPostDocument) {
            $postDateHistoric->setPostDocument($newPostDocument);
        }

        return $this;
    }

    public function getProofReceivedPathForTwig(): string
    {
        return UploadService::Public_post_document_path_show_twig.$this->getPost()->getId().UploadService::Proof_received.$this->getFilename();
    }

    public function getProofReceivedPathForDownload(): string
    {
        return UploadService::Post_Path.$this->getPost()->getId().UploadService::Proof_received.$this->getFilename();
    }
    // use "/post/"
    public function getProofOFProjectInProgressForDownload(): string
    {
        return UploadService::Post_Path.$this->getPost()->getId().UploadService::Proof_project_in_progress.$this->getFilename();
    }

    public function getProofOFProjectInProgressForTwig(): string
    {
        return UploadService::Public_post_document_path_show_twig.$this->getPost()->getId().UploadService::Proof_project_in_progress.$this->getFilename();
    }

    public function getEmailContent(): ?EmailContent
    {
        return $this->EmailContent;
    }

    public function setEmailContent(?EmailContent $EmailContent): self
    {
        $this->EmailContent = $EmailContent;

        return $this;
    }
}
