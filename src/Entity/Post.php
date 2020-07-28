<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PostRepository")
 */
class Post
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     * @Assert\NotBlank(message="ahaahah")
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $content;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $publishedAt;


    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $imageFilename;

    /**
     * @ORM\Column(type="integer")
     */
    private $numberParticipant = 0;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="post", fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    private $comments;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Tag", inversedBy="posts")
     */
    private $tags;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="posts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $uniquekey;

    /**
     * @ORM\OneToMany(targetEntity=Transaction::class, mappedBy="post")
     */
    private $transactions;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $finishAt;

    /**
     * @ORM\ManyToOne(targetEntity=PostStatus::class, inversedBy="posts")
     */
    private $status;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $targetAmount;

    /**
     * @ORM\OneToMany(targetEntity=Favorite::class, mappedBy="post")
     */
    private $favorites;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->transactions = new ArrayCollection();
        $this->favorites = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getPublishedAt(): ?\DateTimeInterface
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTimeInterface $publishedAt): self
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    public function getImageFilename(): ?string
    {
        return $this->imageFilename;
    }
//function added manually to give the Image images/post/$filenaname . After go to twig and change call this method
    public function getImagePath()
    {
        return '/post/image/'.$this->getImageFilename();
    }

    public function getDefaultImagePath()
    {
        return '/post/default_img.jpeg';
    }

    public function setImageFilename(?string $imageFilename): self
    {
        $this->imageFilename = $imageFilename;

        return $this;
    }

    public function getNumberParticipant(): ?int
    {
        return $this->numberParticipant;
    }

    public function setNumberParticipant(int $numberParticipant): self
    {
        $this->numberParticipant = $numberParticipant;

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setPost($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getPost() === $this) {
                $comment->setPost(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Tag[]
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        if ($this->tags->contains($tag)) {
            $this->tags->removeElement($tag);
        }

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

    public function getUniquekey(): ?string
    {
        return $this->uniquekey;
    }

    public function setUniquekey(?string $uniquekey): self
    {
        $this->uniquekey = $uniquekey;

        return $this;
    }

    /**
     * @return Collection|Transaction[]
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Transaction $transaction): self
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions[] = $transaction;
            $transaction->setPost($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): self
    {
        if ($this->transactions->contains($transaction)) {
            $this->transactions->removeElement($transaction);
            // set the owning side to null (unless already changed)
            if ($transaction->getPost() === $this) {
                $transaction->setPost(null);
            }
        }

        return $this;
    }

    public function getFinishAt(): ?\DateTimeInterface
    {
        return $this->finishAt;
    }

    public function setFinishAt(?\DateTimeInterface $finishAt): self
    {
        $this->finishAt = $finishAt;

        return $this;
    }

    public function getStatus(): ?PostStatus
    {
        return $this->status;
    }

    public function setStatus(?PostStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getTargetAmount(): ?int
    {
        return $this->targetAmount;
    }

    public function setTargetAmount(?int $targetAmount): self
    {
        $this->targetAmount = $targetAmount;

        return $this;
    }

    public function getTransactionSum(){

            $amount = $this->getTransactions()->getValues();
         //dd(count($amount));
            $total =0;

            for ($i=0; $i< count($amount); $i++) {
               //var_dump($amount[$i]->getUser());
                //var_dump($amount[$i]->getAmount());
                $total += $amount[$i]->getAmount();

            }
            return $total;

    }

    public function getTransactionSumByUser(User $user){

        $amount = $this->getTransactions()->getValues();
        //dd(count($amount));
        $total =0;

        for ($i=0; $i< count($amount); $i++) {

            if($amount[$i]->getUser() === $user){
                $total += $amount[$i]->getAmount();
                //dump('amount ID'.$amount[$i]->getId());
                //dump($this->getId());
                //dump('amount Total'.$total);
            }
          //  if($amount[$i]->)
            //var_dump($amount[$i]->getAmount());


        }
        return $total;

    }

    public function getTransactionAnonymousSum(){

        $amount = $this->getTransactions()->getValues();
        $total =0;

        for ($i=0; $i< count($amount); $i++) {

            if($amount[$i]->getAnonymousDonation()){
                $total += $amount[$i]->getAmountAfterFees();
            }
        }
        return $total;
    }

    public function getPercentageAdvancement(){
        $amountCollected = $this->getTransactionSum();
        $targetAmount = $this->getTargetAmount();

        $percentage = $amountCollected/$targetAmount * 100;

        return $percentage;
    }

    /**
     * @return Collection|Favorite[]
     */
    public function getFavorites(): Collection
    {
        return $this->favorites;
    }

    public function addFavorite(Favorite $favorite): self
    {
        if (!$this->favorites->contains($favorite)) {
            $this->favorites[] = $favorite;
            $favorite->setPost($this);
        }

        return $this;
    }

    public function removeFavorite(Favorite $favorite): self
    {
        if ($this->favorites->contains($favorite)) {
            $this->favorites->removeElement($favorite);
            // set the owning side to null (unless already changed)
            if ($favorite->getPost() === $this) {
                $favorite->setPost(null);
            }
        }

        return $this;
    }
}
