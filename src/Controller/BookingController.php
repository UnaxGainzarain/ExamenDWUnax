<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Model\BookingNewDTO;
use App\Repository\ActivityRepository;
use App\Repository\BookingRepository;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class BookingController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ClientRepository $clientRepository,
        private ActivityRepository $activityRepository,
        private BookingRepository $bookingRepository
    ) {}

    #[Route('/bookings', name: 'book_activity', methods: ['POST'])]
    public function bookActivity(
        #[MapRequestPayload] BookingNewDTO $bookingDto
    ): JsonResponse
    {
        // 1. Validar que existan Cliente y Actividad (0.5 Puntos)
        $client = $this->clientRepository->find($bookingDto->client_id);
        $activity = $this->activityRepository->find($bookingDto->activity_id);

        if (!$client) {
            return $this->json(['code' => 400, 'description' => 'Client not found'], 400);
        }
        if (!$activity) {
            return $this->json(['code' => 400, 'description' => 'Activity not found'], 400);
        }

        // 2. Validar plazas suficientes (0.5 Puntos)
        // Nota: count() hace una query extra, pero es la forma object-oriented limpia con Doctrine
        if ($activity->getBookings()->count() >= $activity->getMaxParticipants()) {
            return $this->json(['code' => 400, 'description' => 'Activity is full'], 400);
        }

        // 3. Validar tipo de usuario y límite semanal (0.5 Puntos)
        if ($client->getType() === 'standard') {
            // Usamos el método que creamos en el repositorio
            $weeklyBookings = $this->bookingRepository->countBookingsForClientInWeek(
                $client->getId(), 
                $activity->getDateStart()
            );

            // "Los usuarios standard no pueden reservar más de 2 actividades por semana"
            if ($weeklyBookings >= 2) {
                return $this->json([
                    'code' => 400, 
                    'description' => 'Standard clients cannot book more than 2 activities per week'
                ], 400);
            }
        }
        // Si es 'premium', saltamos esta validación ("pueden reservar lo que quieran")

        // 4. Crear la reserva (Persistencia)
        $booking = new Booking();
        $booking->setClient($client);
        $booking->setActivity($activity);

        $this->entityManager->persist($booking);
        $this->entityManager->flush();

        // 5. Devolver respuesta completa (1.5 Puntos)
        // Mapeamos manualmente para cumplir el esquema Booking del YAML y evitar referencias circulares
        
        // Playlist de la actividad para la respuesta anidada
        $playlistData = [];
        foreach ($activity->getPlayList() as $song) {
            $playlistData[] = [
                'id' => $song->getId(),
                'name' => $song->getName(),
                'duration_seconds' => $song->getDurationSeconds()
            ];
        }

        $response = [
            'id' => $booking->getId(),
            'client_id' => $client->getId(), // Según YAML Booking tiene client_id
            'activity' => [                  // Según YAML Booking tiene objeto Activity completo
                'id' => $activity->getId(),
                'max_participants' => $activity->getMaxParticipants(),
                'clients_signed' => $activity->getBookings()->count(),
                'type' => $activity->getType(),
                'play_list' => $playlistData,
                'date_start' => $activity->getDateStart()->format('Y-m-d H:i:s'),
                'date_end' => $activity->getDateEnd()->format('Y-m-d H:i:s'),
            ]
        ];

        return $this->json($response);
    }
}