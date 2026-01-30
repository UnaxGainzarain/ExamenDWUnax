<?php

namespace App\Controller;

use App\Repository\ClientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

class ClientController extends AbstractController
{
    public function __construct(private ClientRepository $clientRepository)
    {}

    #[Route('/clients/{id}', name: 'userInformation', methods: ['GET'])]
    public function userInformation(
        int $id,
        #[MapQueryParameter] bool $with_bookings = false,
        #[MapQueryParameter] bool $with_statistics = false
    ): JsonResponse
    {
        // 1. Recuperar cliente (Punto 4: Path param)
        $client = $this->clientRepository->find($id);

        if (!$client) {
            return $this->json(['code' => 404, 'description' => 'Client not found'], 404);
        }

        // 2. Construir información básica (Punto 4: nombre, email, tipo)
        $response = [
            'id' => $client->getId(),
            'type' => $client->getType(),
            'name' => $client->getName(),
            'email' => $client->getEmail(),
            
        ];

        // 3. Lógica condicional: Reservas (with_bookings)
        if ($with_bookings) {
            $bookingsData = [];
            foreach ($client->getBookings() as $booking) {
                $activity = $booking->getActivity();
                
                // Reutilizamos la lógica de formateo de Activity del punto anterior
                // (En un proyecto real, esto iría a un Servicio o Serializer)
                $playlistData = [];
                foreach ($activity->getPlayList() as $song) {
                    $playlistData[] = [
                        'id' => $song->getId(),
                        'name' => $song->getName(),
                        'duration_seconds' => $song->getDurationSeconds()
                    ];
                }

                $bookingsData[] = [
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
            }
            $response['activities_booked'] = $bookingsData;
        }

        // 4. Lógica condicional: Estadísticas (with_statistics)
        if ($with_statistics) {
            // Agrupamos datos en memoria: $stats[2025]['BodyPump'] = ['count'=>1, 'minutes'=>60]
            $tempStats = [];

            foreach ($client->getBookings() as $booking) {
                $activity = $booking->getActivity();
                $year = $activity->getDateStart()->format('Y');
                $type = $activity->getType();

                // Calcular duración en minutos
                // dateEnd - dateStart
                $durationDiff = $activity->getDateEnd()->diff($activity->getDateStart());
                // Convertir diff a minutos totales (horas * 60 + minutos)
                $minutes = ($durationDiff->h * 60) + $durationDiff->i;

                // Inicializar si no existe
                if (!isset($tempStats[$year])) {
                    $tempStats[$year] = [];
                }
                if (!isset($tempStats[$year][$type])) {
                    $tempStats[$year][$type] = ['num_activities' => 0, 'num_minutes' => 0];
                }

                // Acumular
                $tempStats[$year][$type]['num_activities']++;
                $tempStats[$year][$type]['num_minutes'] += $minutes;
            }

            // Formatear al esquema del YAML (StatisticsByYear -> StatisticsByType)
            $statisticsData = [];
            foreach ($tempStats as $year => $typesData) {
                $statsByType = [];
                foreach ($typesData as $type => $metrics) {
                    $statsByType[] = [
                        'type' => $type,
                        'statistics' => [
                            [
                                'num_activities' => $metrics['num_activities'],
                                'num_minutes' => $metrics['num_minutes']
                            ]
                        ]
                    ];
                }

                $statisticsData[] = [
                    'year' => (int)$year,
                    'statistics_by_type' => $statsByType
                ];
            }
            
            $response['activity_statistics'] = $statisticsData;
        }

        return $this->json($response);
    }
}