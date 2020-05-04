<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FilesRepository")
 */
class Files
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="date")
     */
    private $Ajout;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $upload_name;

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

    public function getAjout(): ?\DateTimeInterface
    {
        return $this->Ajout;
    }

    public function setAjout(\DateTimeInterface $Ajout): self
    {
        $this->Ajout = $Ajout;

        return $this;
    }

    public function getUploadName(): ?string
    {
        return $this->upload_name;
    }

    public function setUploadName(string $upload_name): self
    {
        $this->upload_name = $upload_name;

        return $this;
    }
}
