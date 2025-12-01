<?php
// neu
namespace Koboldsoft\AiReportBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\AiReportBundle\Repository\MmAuftragRepository")
 * @ORM\Table(name="mm_auftrag")
 */
class MmAuftrag
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", nullable=false)
     */
    private $id;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $einheiten_ist;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEinheitenIst(): ?float
    {
        return $this->einheiten_ist;
    }

    public function setEinheitenIst(?float $einheitenIst): self
    {
        $this->einheiten_ist = $einheitenIst;
        return $this;
    }
}
