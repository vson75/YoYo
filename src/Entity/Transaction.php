<?php

namespace App\Entity;

use App\Repository\TransactionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TransactionRepository::class)
 */
class Transaction
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="transactions")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Post::class, inversedBy="transactions")
     */
    private $post;

    /**
     * @ORM\Column(type="datetime")
     */
    private $transfertAt;

    /**
     * @ORM\Column(type="float")
     */
    private $amount;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $anonymousDonation = 0;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $clientSecret;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $fees;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $customDonationForSite;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $amountAfterFees;

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

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): self
    {
        $this->post = $post;

        return $this;
    }

    public function getTransfertAt(): ?\DateTimeInterface
    {
        return $this->transfertAt;
    }

    public function setTransfertAt(\DateTimeInterface $transfertAt): self
    {
        $this->transfertAt = $transfertAt;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getAnonymousDonation(): ?bool
    {
        return $this->anonymousDonation;
    }

    public function setAnonymousDonation(?bool $anonymousDonation): self
    {
        $this->anonymousDonation = $anonymousDonation;

        return $this;
    }

    public function getClientSecret(): ?string
    {
        return $this->clientSecret;
    }

    public function setClientSecret(string $clientSecret): self
    {
        $this->clientSecret = $clientSecret;

        return $this;
    }

    public function getFees(): ?float
    {
        return $this->fees;
    }

    public function setFees(?float $fees): self
    {
        $this->fees = $fees;

        return $this;
    }


    public function getCustomDonationForSite(): ?float
    {
        return $this->customDonationForSite;
    }

    public function setCustomDonationForSite(?float $customDonationForSite): self
    {
        $this->customDonationForSite = $customDonationForSite;

        return $this;
    }

    public function getAmountAfterFees(): ?float
    {
        return $this->amountAfterFees;
    }

    public function setAmountAfterFees(?float $amountAfterFees): self
    {
        $this->amountAfterFees = $amountAfterFees;

        return $this;
    }
}
