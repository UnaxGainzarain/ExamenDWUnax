<?php

namespace App\Repository;

use App\Entity\Activity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Activity>
 */
class ActivityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Activity::class);
    }

    // Método para cumplir con el punto 1 y 3 del examen
    public function findByFilters(?string $type, bool $onlyFree, int $page, int $pageSize, string $sort, string $order): array
    {
        $qb = $this->createQueryBuilder('a');

        // 1. Filtro por tipo (Punto 1)
        if ($type) {
            $qb->andWhere('a.type = :type')
               ->setParameter('type', $type);
        }

        // 2. Filtro de plazas libres (Punto 3 - onlyfree)
        // Se complica un poco porque hay que contar bookings. Usamos size() de la colección en query
        if ($onlyFree) {
            $qb->leftJoin('a.bookings', 'b')
               ->groupBy('a.id')
               ->having('COUNT(b.id) < a.maxParticipants');
        }

        // 3. Ordenación (Punto 1)
        // El enunciado dice 'date', mapeamos a 'dateStart'
        $sortField = 'a.dateStart'; 
        if ($sort === 'date') {
            $sortField = 'a.dateStart';
        }
        
        $qb->orderBy($sortField, $order);

        // 4. Paginación (Punto 1)
        $qb->setFirstResult(($page - 1) * $pageSize)
           ->setMaxResults($pageSize);

        return $qb->getQuery()->getResult();
    }
    
    // Método auxiliar para contar total (para la meta-paginación)
    public function countByFilters(?string $type, bool $onlyFree): int
    {
        $qb = $this->createQueryBuilder('a')
                   ->select('count(distinct a.id)');

        if ($type) {
            $qb->andWhere('a.type = :type')->setParameter('type', $type);
        }
        
        if ($onlyFree) {
             $qb->leftJoin('a.bookings', 'b')
               ->groupBy('a.id')
               ->having('COUNT(b.id) < a.maxParticipants');
             
             // Nota: count con having en doctrine a veces requiere lógica extra, 
             // pero para simplificar en examen devolvemos el count simple o filtered.
             // Si da problemas, se puede recuperar result y hacer count($result).
             return count($this->findByFilters($type, $onlyFree, 1, 10000, 'date', 'asc')); 
        }

        return $qb->getQuery()->getSingleScalarResult();
    }
}