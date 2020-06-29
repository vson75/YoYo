<?php

namespace App\Entity;

use App\Repository\PostStatusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PostStatusRepository::class)
 */
class PostStatus
{
    public const POST_DRAFT = 1;
    public const POST_SUBMIT_TO_ADMIN = 2;
    public const POST_WAITING_INFO = 3;
    public const POST_COLLECTING = 4;
    public const POST_TRANSFERT_FUND = 5;
    public const POST_CLOSE = 6;
    public const POST_STOP = 7;


    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity=Post::class, mappedBy="status")
     */
    private $posts;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection|Post[]
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): self
    {
        if (!$this->posts->contains($post)) {
            $this->posts[] = $post;
            $post->setStatus($this);
        }

        return $this;
    }

    public function removePost(Post $post): self
    {
        if ($this->posts->contains($post)) {
            $this->posts->removeElement($post);
            // set the owning side to null (unless already changed)
            if ($post->getStatus() === $this) {
                $post->setStatus(null);
            }
        }

        return $this;
    }
}
