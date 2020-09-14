<?php

namespace App\Entity;

use App\Repository\EmailContentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EmailContentRepository::class)
 */
class EmailContent
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $Object;

    /**
     * @ORM\Column(type="text")
     */
    private $Content;

    /**
     * @ORM\OneToMany(targetEntity=PostDocument::class, mappedBy="EmailContent")
     */
    private $postDocuments;

    /**
     * @ORM\ManyToOne(targetEntity=Post::class)
     */
    private $post;

    public function __construct()
    {
        $this->postDocuments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getObject(): ?string
    {
        return $this->Object;
    }

    public function setObject(string $Object): self
    {
        $this->Object = $Object;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->Content;
    }

    public function setContent(string $Content): self
    {
        $this->Content = $Content;

        return $this;
    }

    /**
     * @return Collection|PostDocument[]
     */
    public function getPostDocuments(): Collection
    {
        return $this->postDocuments;
    }

    public function addPostDocument(PostDocument $postDocument): self
    {
        if (!$this->postDocuments->contains($postDocument)) {
            $this->postDocuments[] = $postDocument;
            $postDocument->setEmailContent($this);
        }

        return $this;
    }

    public function removePostDocument(PostDocument $postDocument): self
    {
        if ($this->postDocuments->contains($postDocument)) {
            $this->postDocuments->removeElement($postDocument);
            // set the owning side to null (unless already changed)
            if ($postDocument->getEmailContent() === $this) {
                $postDocument->setEmailContent(null);
            }
        }

        return $this;
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
}
