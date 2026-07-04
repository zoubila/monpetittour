<?php

declare(strict_types=1);

namespace App\Domain\Enum;

enum RiderSpecialty: string
{
    case Climber = 'grimpeur';
    case Sprinter = 'sprinteur';
    case TimeTrialist = 'rouleur';
    case Leader = 'leader';
    case Domestique = 'équipier';
}
