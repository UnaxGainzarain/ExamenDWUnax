<?php

namespace App\Repository;

use App\Entity\Booking;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


class BookingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Booking::class);
    }

    // Método auxiliar para la validación de usuarios STANDARD (Máx 2 por semana)
    public function countBookingsForClientInWeek(int $clientId, \DateTimeInterface $activityDate): int
    {
        // Calcular inicio (Lunes) y fin (Domingo) de la semana de la actividad
        $startOfWeek = (clone $activityDate)->modify('monday this week')->setTime(0, 0, 0);
        $endOfWeek   = (clone $activityDate)->modify('sunday this week')->setTime(23, 59, 59);

        return $this->createQueryBuilder('b')
            ->select('count(b.id)')
            ->join('b.activity', 'a') 
            ->where('b.client = :clientId')
            ->andWhere('a.dateStart BETWEEN :start AND :end')
            ->setParameter('clientId', $clientId)
            ->setParameter('start', $startOfWeek)
            ->setParameter('end', $endOfWeek)
            ->getQuery()
            ->getSingleScalarResult();
    }
}