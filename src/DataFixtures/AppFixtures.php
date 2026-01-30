<?php

namespace App\DataFixtures;

use App\Entity\Activity;
use App\Entity\Booking;
use App\Entity\Client;
use App\Entity\Song;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // 1. CREAR CLIENTES
        
        // Cliente Standard (Para probar límite de 2 reservas/semana)
        $clientStandard = new Client();
        $clientStandard->setName('Miguel Standard');
        $clientStandard->setEmail('miguel.std@4vgym.com');
        $clientStandard->setType('standard');
        $manager->persist($clientStandard);

        // Cliente Premium (Para probar que no tiene límites)
        $clientPremium = new Client();
        $clientPremium->setName('Laura Premium');
        $clientPremium->setEmail('laura.pro@4vgym.com');
        $clientPremium->setType('premium');
        $manager->persist($clientPremium);

        // 2. CREAR ACTIVIDADES Y CANCIONES

        // Actividad 1: BodyPump en el Pasado (2024) -> Para probar Estadísticas (Punto 4)
        $actPasada = new Activity();
        $actPasada->setType('BodyPump');
        $actPasada->setMaxParticipants(20);
        $actPasada->setDateStart(new \DateTime('2024-05-10 10:00:00'));
        $actPasada->setDateEnd(new \DateTime('2024-05-10 11:00:00')); // 60 min
        $manager->persist($actPasada);

        // Actividad 2: Spinning Futura (Semana actual o futura) -> Para reservar (Punto 2)
        // La ponemos la próxima semana para asegurar que es futuro
        $actFutura1 = new Activity();
        $actFutura1->setType('Spinning');
        $actFutura1->setMaxParticipants(5); // Pocas plazas para probar "Activity full"
        $actFutura1->setDateStart(new \DateTime('+1 week 10:00:00'));
        $actFutura1->setDateEnd(new \DateTime('+1 week 10:45:00'));
        
        // Añadir Playlist (Punto 1: relación 1-M)
        $song1 = new Song();
        $song1->setName('Eye of the Tiger');
        $song1->setDurationSeconds(240);
        $song1->setActivity($actFutura1); // Lado propietario de la relación (si fuera bidireccional puro)
        $manager->persist($song1);
        
        // Importante: Si la relación en Activity tiene cascade=['persist'], bastaría con añadir a la colección,
        // pero asignando el padre explícitamente es más seguro en Doctrine básico.
        
        $manager->persist($actFutura1);

        // Actividad 3: Core Futura (Misma semana que la anterior) -> Para probar límite semanal
        $actFutura2 = new Activity();
        $actFutura2->setType('Core');
        $actFutura2->setMaxParticipants(20);
        $actFutura2->setDateStart(new \DateTime('+1 week 12:00:00')); // Misma semana
        $actFutura2->setDateEnd(new \DateTime('+1 week 12:30:00'));
        $manager->persist($actFutura2);

        // 3. CREAR RESERVAS (BOOKINGS)

        // Reservamos la actividad pasada para el cliente Standard (generará estadísticas)
        $booking1 = new Booking();
        $booking1->setClient($clientStandard);
        $booking1->setActivity($actPasada);
        $manager->persist($booking1);

        // Reservamos una actividad futura para llenar cupo (prueba de onlyfree)
        // Llenamos 1 plaza de la actividad de spinning
        $booking2 = new Booking();
        $booking2->setClient($clientPremium);
        $booking2->setActivity($actFutura1);
        $manager->persist($booking2);

        // Guardar todo en BBDD
        $manager->flush();
    }
}