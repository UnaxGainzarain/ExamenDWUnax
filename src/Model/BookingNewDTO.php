<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class BookingNewDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Positive]
        public int $activity_id,

        #[Assert\NotBlank]
        #[Assert\Positive]
        public int $client_id
    ) {}
}