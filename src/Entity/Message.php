<?php

namespace App\Entity;

use App\Entity\Group;
use App\Entity\User;
use App\Repository\Intranet\Tool\Chat\MessageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;
use DateTimeInterface;

/**
 * @ORM\Table(name="chat_message")
 * @ORM\Entity(repositoryClass="App\Repository\MessageRepository")
 */
class Message
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity=Group::class, inversedBy="messages")
     * @ORM\JoinTable(name="chat_group_message",
     *      joinColumns={@ORM\JoinColumn(name="message_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")}
     *      )
     */
    private $message_to_groups;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="messages")
     * @ORM\JoinColumn(nullable=false)
     */
    private $app_user;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $message;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime")
     */
    private $update_at;

    public function __construct()
    {
        $this->message_to_groups = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection|Group[]
     */
    public function getMessageToGroups(): Collection
    {
        return $this->message_to_groups;
    }

    public function addMessageToGroups(Group $message_to_group): self
    {
        if (!$this->message_to_groups->contains($message_to_group)) {
            $this->message_to_groups[] = $message_to_group;
        }

        return $this;
    }

    public function removeMessageToGroups(Group $message_to_group): self
    {
        $this->message_to_groups->removeElement($message_to_group);

        return $this;
    }

    public function getAppUser(): ?User
    {
        return $this->app_user;
    }

    public function setAppUser(?User $app_user): self
    {
        $this->app_user = $app_user;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

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

    public function getUpdateAt(): ?DateTimeInterface
    {
        return $this->update_at;
    }

    public function setUpdateAt(DateTimeInterface $update_at): self
    {
        $this->update_at = $update_at;

        return $this;
    }

}