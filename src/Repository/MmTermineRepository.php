<?php

namespace Koboldsoft\AiReportBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Koboldsoft\AiReportBundle\Entity\MmTermine;

class MmTermineRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MmTermine::class);
    }
    
    public function findByAuftrag(int $auftragId): array
    {
        return $this->findBy(['id_auftrag' => $auftragId]);
    }
}
