<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\Email()
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string", nullable=true)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $facebookId;

    /**
     * @ORM\OneToMany(targetEntity=Post::class, mappedBy="user")
     */
    private $posts;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="user")
     */
    private $comments;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $token;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $tokencreateAt;

    /**
     * @ORM\OneToMany(targetEntity=UserDocument::class, mappedBy="user")
     */
    private $userDocuments;

    /**
     * @ORM\OneToMany(targetEntity=Transaction::class, mappedBy="user")
     */
    private $transactions;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $icon;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isOrganisation;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $askOrganisation;


    /**
     * @ORM\OneToMany(targetEntity=OrganisationDocument::class, mappedBy="user")
     */
    private $organisationDocuments;

    /**
     * @ORM\OneToOne(targetEntity=RequestOrganisationInfo::class, mappedBy="user", cascade={"persist", "remove"})
     */
    private $requestOrganisationInfo;

    /**
     * @ORM\OneToMany(targetEntity=RequestOrganisationDocument::class, mappedBy="user")
     */
    private $requestOrganisationDocuments;

    /**
     * @ORM\OneToMany(targetEntity=Favorite::class, mappedBy="user")
     */
    private $favorites;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->userDocuments = new ArrayCollection();
        $this->transactions = new ArrayCollection();
        $this->RequestOrganisationDocument = new ArrayCollection();
        $this->organisationDocuments = new ArrayCollection();
        $this->requestOrganisationDocuments = new ArrayCollection();
        $this->favorites = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFacebookId(): ?int
    {
        return $this->facebookId;
    }

    public function setFacebookId(?int $facebookId): self
    {
        $this->facebookId = $facebookId;

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
            $post->setUser($this);
        }

        return $this;
    }

    public function removePost(Post $post): self
    {
        if ($this->posts->contains($post)) {
            $this->posts->removeElement($post);
            // set the owning side to null (unless already changed)
            if ($post->getUser() === $this) {
                $post->setUser(null);
            }
        }

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
            $comment->setUser($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getUser() === $this) {
                $comment->setUser(null);
            }
        }

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getTokencreateAt(): ?\DateTimeInterface
    {
        return $this->tokencreateAt;
    }

    public function setTokencreateAt(?\DateTimeInterface $tokencreateAt): self
    {
        $this->tokencreateAt = $tokencreateAt;

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
            $userDocument->setUser($this);
        }

        return $this;
    }

    public function removeUserDocument(UserDocument $userDocument): self
    {
        if ($this->userDocuments->contains($userDocument)) {
            $this->userDocuments->removeElement($userDocument);
            // set the owning side to null (unless already changed)
            if ($userDocument->getUser() === $this) {
                $userDocument->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Transaction[]
     */
    public function getPost(): Collection
    {
        return $this->post;
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
            $transaction->setUser($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): self
    {
        if ($this->transactions->contains($transaction)) {
            $this->transactions->removeElement($transaction);
            // set the owning side to null (unless already changed)
            if ($transaction->getUser() === $this) {
                $transaction->setUser(null);
            }
        }

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    //function added manually to give the Image images/post/$filenaname . After go to twig and change call this method
    public function getImagePath()
    {
        return 'uploads/user/icon/'.$this->getId().'/'.$this->getIcon();
    }

    public function setIcon(?string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function getIsOrganisation(): ?bool
    {
        return $this->isOrganisation;
    }

    public function setIsOrganisation(?bool $isOrganisation): self
    {
        $this->isOrganisation = $isOrganisation;

        return $this;
    }

    public function getAskOrganisation(): ?bool
    {
        return $this->askOrganisation;
    }

    public function setAskOrganisation(?bool $askOrganisation): self
    {
        $this->askOrganisation = $askOrganisation;

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
            $organisationDocument->setUser($this);
        }

        return $this;
    }

    public function removeOrganisationDocument(OrganisationDocument $organisationDocument): self
    {
        if ($this->organisationDocuments->contains($organisationDocument)) {
            $this->organisationDocuments->removeElement($organisationDocument);
            // set the owning side to null (unless already changed)
            if ($organisationDocument->getUser() === $this) {
                $organisationDocument->setUser(null);
            }
        }

        return $this;
    }

    public function getRequestOrganisationInfo(): ?RequestOrganisationInfo
    {
        return $this->requestOrganisationInfo;
    }

    public function setRequestOrganisationInfo(?RequestOrganisationInfo $requestOrganisationInfo): self
    {
        $this->requestOrganisationInfo = $requestOrganisationInfo;

        // set (or unset) the owning side of the relation if necessary
        $newUserId = null === $requestOrganisationInfo ? null : $this;
        if ($requestOrganisationInfo->getUser() !== $newUserId) {
            $requestOrganisationInfo->setUser($newUserId);
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
            $requestOrganisationDocument->setUser($this);
        }

        return $this;
    }

    public function removeRequestOrganisationDocument(RequestOrganisationDocument $requestOrganisationDocument): self
    {
        if ($this->requestOrganisationDocuments->contains($requestOrganisationDocument)) {
            $this->requestOrganisationDocuments->removeElement($requestOrganisationDocument);
            // set the owning side to null (unless already changed)
            if ($requestOrganisationDocument->getUser() === $this) {
                $requestOrganisationDocument->setUser(null);
            }
        }

        return $this;
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
            $favorite->setUser($this);
        }

        return $this;
    }

    public function removeFavorite(Favorite $favorite): self
    {
        if ($this->favorites->contains($favorite)) {
            $this->favorites->removeElement($favorite);
            // set the owning side to null (unless already changed)
            if ($favorite->getUser() === $this) {
                $favorite->setUser(null);
            }
        }

        return $this;
    }
}
