<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\DocumentRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @ORM\Entity(repositoryClass=DocumentRepository::class)
 */
class Document
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
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity=Association::class, inversedBy="document")
     */
    private $association;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $caption;

    private $document_file;

    public function getDocumentFile()
    {
        return $this->document_file;
    }

    public function setDocumentFile(UploadedFile $document_file = null)
    {
        $this->document_file = $document_file;
    }

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

    public function getAssociation(): ?Association
    {
        return $this->association;
    }

    public function setAssociation(?Association $association): self
    {
        $this->association = $association;

        return $this;
    }

    public function getCaption(): ?string
    {
        return $this->caption;
    }

    public function setCaption(string $caption): self
    {
        $this->caption = $caption;

        return $this;
    }
}
