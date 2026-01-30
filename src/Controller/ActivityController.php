<?php

namespace App\Controller;

use App\Repository\ActivityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;

class ActivityController extends AbstractController
{
    // Inyectamos el repositorio en el constructor como vimos en clase (Servicios/DI)
    public function __construct(private ActivityRepository $activityRepository)
    {}

    #[Route('/activities', name: 'find_activities', methods: ['GET'])]
    public function findActivities(
        // Utilizamos MapQueryParameter para capturar los parámetros de la URL 
        #[MapQueryParameter] int $page = 1,
        #[MapQueryParameter] int $page_size = 10,
        #[MapQueryParameter] string $sort = 'date',
        #[MapQueryParameter] string $order = 'desc',
        #[MapQueryParameter] ?string $type = null, // Puede ser nulo para ver todos
        #[MapQueryParameter] bool $onlyfree = true // Por defecto true según enunciado 
    ): JsonResponse
    {
        // 1. Validaciones manuales (Clean Coding: Fail Fast)
        // Validar orden 
        if (!in_array(strtolower($order), ['asc', 'desc'])) {
             return $this->json(['code' => 400, 'description' => 'Order must be asc or desc'], 400);
        }
        
        // Validar tipo si viene informado 
    
        $validTypes = ['BodyPump', 'Spinning', 'Core'];
        if ($type && !in_array($type, $validTypes)) {
            return $this->json(['code' => 400, 'description' => 'Invalid activity type'], 400);
        }

        // 2. Llamada a BBDD usando el repositorio (Punto 1 y 3)
        // Usamos el método findByFilters creado en el paso anterior
        $activities = $this->activityRepository->findByFilters($type, $onlyfree, $page, $page_size, $sort, $order);
        
        // Recuperamos el total para la paginación
        $totalItems = $this->activityRepository->countByFilters($type, $onlyfree);

        // 3. Mapeo manual de la respuesta para cumplir con el YAML (ActivityList)
        // Convertimos las Entidades a un array asociativo limpio
        $data = [];
        foreach ($activities as $activity) {
            
            // Convertimos la Playlist (Relación 1-M) a array 
            $playlistData = [];
            foreach ($activity->getPlayList() as $song) {
                $playlistData[] = [
                    'id' => $song->getId(),
                    'name' => $song->getName(),
                    'duration_seconds' => $song->getDurationSeconds()
                ];
            }

            // Calculamos clients_signed (Punto 3) contando la colección de reservas 
            $clientsSigned = $activity->getBookings()->count();

            $data[] = [
                'id' => $activity->getId(),
                'max_participants' => $activity->getMaxParticipants(),
                'clients_signed' => $clientsSigned, // Campo calculado
                'type' => $activity->getType(),
                'play_list' => $playlistData,
                'date_start' => $activity->getDateStart()->format('Y-m-d H:i:s'),
                'date_end' => $activity->getDateEnd()->format('Y-m-d H:i:s'),
            ];
        }

        // 4. Construcción de la respuesta JSON final con metadatos 
        $response = [
            'data' => $data,
            'meta' => [
                [
                    'page' => $page,
                    'limit' => $page_size,
                    'total-items' => $totalItems
                ]
            ]
        ];

        return $this->json($response);
    }
}