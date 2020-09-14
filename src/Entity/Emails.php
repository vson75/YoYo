<?php

namespace App\Entity;

use App\Repository\EmailsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EmailsRepository::class)
 */
class Emails
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $sentDate;

    /**
     * @ORM\ManyToOne(targetEntity=EmailContent::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $emailContent;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $userRecipient;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSentDate(): ?\DateTimeInterface
    {
        return $this->sentDate;
    }

    public function setSentDate(?\DateTimeInterface $sentDate): self
    {
        $this->sentDate = $sentDate;

        return $this;
    }

    public function getEmailContent(): ?EmailContent
    {
        return $this->emailContent;
    }

    public function setEmailContent(?EmailContent $emailContent): self
    {
        $this->emailContent = $emailContent;

        return $this;
    }

    public function getUserRecipient(): ?User
    {
        return $this->userRecipient;
    }

    public function setUserRecipient(?User $userRecipient): self
    {
        $this->userRecipient = $userRecipient;

        return $this;
    }
}
