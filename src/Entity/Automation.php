<?php

namespace App\Entity;

use App\Repository\AutomationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AutomationRepository::class)
 */
class Automation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @ORM\Column(type="boolean")
     */
    private $enabled;

    /**
     * @ORM\Column(type="boolean")
     */
    private $repeatActivation;

    /**
     * @ORM\Column(type="text")
     */
    private $ifJson;

    /**
     * @ORM\Column(type="text")
     */
    private $actionsJson;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getRepeatActivation()
    {
        return $this->repeatActivation;
    }

    public function setRepeatActivation($repeatActivation): void
    {
        $this->repeatActivation = $repeatActivation;
    }

    public function getIfJson(): ?string
    {
        return $this->ifJson;
    }

    public function setIfJson(string $ifJson): self
    {
        $this->ifJson = $ifJson;

        return $this;
    }

    public function getActionsJson(): ?string
    {
        return $this->actionsJson;
    }

    public function setActionsJson(string $actionsJson): self
    {
        $this->actionsJson = $actionsJson;

        return $this;
    }
}
