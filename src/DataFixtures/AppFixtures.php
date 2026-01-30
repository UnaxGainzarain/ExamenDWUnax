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
        
        $clientStandard = new Client();
        $clientStandard->setName('Miguel Standard');
        $clientStandard->setEmail('miguel.std@4vgym.com');
        $clientStandard->setType('standard');
        $manager->persist($clientStandard);

        $clientPremium = new Client();
        $clientPremium->setName('Laura Premium');
        $clientPremium->setEmail('laura.pro@4vgym.com');
        $clientPremium->setType('premium');
        $manager->persist($clientPremium);

       
        $actPasada = new Activity();
        $actPasada->setType('BodyPump');
        $actPasada->setMaxParticipants(20);
        $actPasada->setDateStart(new \DateTime('2024-05-10 10:00:00'));
        $actPasada->setDateEnd(new \DateTime('2024-05-10 11:00:00')); // 60 min
        $manager->persist($actPasada);

       
        $actFutura1 = new Activity();
        $actFutura1->setType('Spinning');
        $actFutura1->setMaxParticipants(5); 
        $actFutura1->setDateStart(new \DateTime('+1 week 10:00:00'));
        $actFutura1->setDateEnd(new \DateTime('+1 week 10:45:00'));
        
        $song1 = new Song();
        $song1->setName('Eye of the Tiger');
        $song1->setDurationSeconds(240);
        $song1->setActivity($actFutura1); 
        $manager->persist($song1);
        
        $manager->persist($actFutura1);

        $actFutura2 = new Activity();
        $actFutura2->setType('Core');
        $actFutura2->setMaxParticipants(20);
        $actFutura2->setDateStart(new \DateTime('+1 week 12:00:00')); // Misma semana
        $actFutura2->setDateEnd(new \DateTime('+1 week 12:30:00'));
        $manager->persist($actFutura2);


        $booking1 = new Booking();
        $booking1->setClient($clientStandard);
        $booking1->setActivity($actPasada);
        $manager->persist($booking1);

      
        $booking2 = new Booking();
        $booking2->setClient($clientPremium);
        $booking2->setActivity($actFutura1);
        $manager->persist($booking2);

        // Guardar todo en BBDD
        $manager->flush();
    }
}