<?php

namespace App\Entity;

use App\Entity\Group;
use App\Entity\Message;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;
use DateTimeInterface;

// Il y a un problème ici dans l'annotation je crois que c'est les guillemet
/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(fields="username", message="Le nom d'utilisateur est déjà utilisée")
 * @UniqueEntity(fields="email", message="L'email est déjà utilisée")
 * @method string getUserIdentifier()
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min=8, minMessage="Votre mot de passe doit faire minimun 8 caractères")
     * @Assert\EqualTo (propertyPath="confirm_password", message="Vous n'avais pas rentrer le même mot de passe")
     */
    private $password;

    /**
     *
     */
    public $confirm_password;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $pp;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updated_at;

    /**
     * @ORM\ManyToMany(targetEntity=Group::class, mappedBy="user_to_groups")
     */
    private $groups;

    /**
     * @ORM\OneToMany(targetEntity=Message::class, mappedBy="app_user")
     */
    private $messages;

    public function __construct()
    {
        $this->conversations = new ArrayCollection();
        $this->chats = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
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

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getPP(): ?string
    {
        return $this->pp;
    }

    public function setPP(string $pp): self
    {
        $this->pp = $pp;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(DateTimeImmutable $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    /**
     * @return Collection|Group[]
     */
    public function getGroups(): Collection
    {
        return $this->groups;
    }

    public function addConversation(Group $group): self
    {
        if (!$this->groups->contains($group)) {
            $this->groups[] = $group;
            $group->addUserToGroups($this);
        }

        return $this;
    }

    public function removeConversation(Group $conversation): self
    {
        if ($this->conversations->removeElement($conversation)) {
            $conversation->removeUserToGroups($this);
        }

        return $this;
    }

    /**
     * @return Collection|Message[]
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addChat(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages[] = $message;
            $message->setAppUser($this);
        }

        return $this;
    }

    public function removeChat(Message $message): self
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getAppUser() === $this) {
                $message->setAppUser(null);
            }
        }

        return $this;
    }

    public function getRoles(): array
    {
        return ['ROLE_User'];
    }

    public function getSalt()
    {
        // TODO: Implement getSalt() method.
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function __call($name, $arguments)
    {
        return $this->username;
    }
}
