<?php

namespace App\Entity;

use App\Repository\WebsiteLanguageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=WebsiteLanguageRepository::class)
 */
class WebsiteLanguage
{
    public const lang_en = 1;
    public const lang_fr = 2;
    public const lang_other = 3;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $lang;

    /**
     * @ORM\OneToMany(targetEntity=Post::class, mappedBy="lang")
     */
    private $posts;

    /**
     * @ORM\OneToMany(targetEntity=PostTranslation::class, mappedBy="lang")
     */
    private $postTranslations;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->postTranslations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLang(): ?string
    {
        return $this->lang;
    }

    public function setLang(string $lang): self
    {
        $this->lang = $lang;

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
            $post->setLang($this);
        }

        return $this;
    }

    public function removePost(Post $post): self
    {
        if ($this->posts->contains($post)) {
            $this->posts->removeElement($post);
            // set the owning side to null (unless already changed)
            if ($post->getLang() === $this) {
                $post->setLang(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|PostTranslation[]
     */
    public function getPostTranslations(): Collection
    {
        return $this->postTranslations;
    }

    public function addPostTranslation(PostTranslation $postTranslation): self
    {
        if (!$this->postTranslations->contains($postTranslation)) {
            $this->postTranslations[] = $postTranslation;
            $postTranslation->setLang($this);
        }

        return $this;
    }

    public function removePostTranslation(PostTranslation $postTranslation): self
    {
        if ($this->postTranslations->contains($postTranslation)) {
            $this->postTranslations->removeElement($postTranslation);
            // set the owning side to null (unless already changed)
            if ($postTranslation->getLang() === $this) {
                $postTranslation->setLang(null);
            }
        }

        return $this;
    }
}
