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
        $client = $this->clientRepository->find($bookingDto->client_id);
        $activity = $this->activityRepository->find($bookingDto->activity_id);

        if (!$client) {
            return $this->json(['code' => 400, 'description' => 'Client not found'], 400);
        }
        if (!$activity) {
            return $this->json(['code' => 400, 'description' => 'Activity not found'], 400);
        }

        
        if ($activity->getBookings()->count() >= $activity->getMaxParticipants()) {
            return $this->json(['code' => 400, 'description' => 'Activity is full'], 400);
        }

        if ($client->getType() === 'standard') {
            $weeklyBookings = $this->bookingRepository->countBookingsForClientInWeek(
                $client->getId(), 
                $activity->getDateStart()
            );

            if ($weeklyBookings >= 2) {
                return $this->json([
                    'code' => 400, 
                    'description' => 'Standard clients cannot book more than 2 activities per week'
                ], 400);
            }
        }
     
        $booking = new Booking();
        $booking->setClient($client);
        $booking->setActivity($activity);

        $this->entityManager->persist($booking);
        $this->entityManager->flush();

        
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
            'client_id' => $client->getId(), 
            'activity' => [                  
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