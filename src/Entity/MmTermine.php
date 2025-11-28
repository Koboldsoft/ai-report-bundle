<?php

namespace Koboldsoft\AiReportBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\AiReportBundle\Repository\MmTermineRepository")
 * @ORM\Table(name="mm_termine")
 */
class MmTermine
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private $id_auftrag;

    
    
    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $einheiten_ist;
    
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $notizen;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdAuftrag(): ?int
    {
        return $this->id_auftrag;
    }
    
    public function setIdAuftrag(int $idAuftrag): self
    {
        $this->id_auftrag = $idAuftrag;
        return $this;
    }
    
    public function getEinheitenIst(): ?float
    {
        return $this->einheiten_ist;
    }
    
    public function setEinheitenIst(?float $einheiten_ist): self
    {
        $this->einheiten_ist = $einheiten_ist;
        return $this;
    }

    public function getNotizen(): ?string
    {
        return $this->notizen;
    }

    public function setNotizen(?string $notizen): self
    {
        $this->notizen = $notizen;
        return $this;
    }
}
