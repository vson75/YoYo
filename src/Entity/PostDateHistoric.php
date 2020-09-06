<?php

namespace App\Entity;

use App\Repository\PostDateHistoricRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PostDateHistoricRepository::class)
 */
class PostDateHistoric
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
     * @ORM\ManyToOne(targetEntity=PostDateType::class)
     */
    private $postDateType;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\OneToOne(targetEntity=PostDocument::class, inversedBy="postDateHistoric", cascade={"persist", "remove"})
     */
    private $postDocument;

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

    public function getPostDateType(): ?PostDateType
    {
        return $this->postDateType;
    }

    public function setPostDateType(?PostDateType $postDateType): self
    {
        $this->postDateType = $postDateType;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): self
    {
        $this->date = $date;

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

    public function getPostDocument(): ?PostDocument
    {
        return $this->postDocument;
    }

    public function setPostDocument(?PostDocument $postDocument): self
    {
        $this->postDocument = $postDocument;

        return $this;
    }
}
