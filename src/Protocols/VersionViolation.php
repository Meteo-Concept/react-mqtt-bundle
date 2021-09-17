<?php

declare(strict_types=1);

namespace MeteoConcept\ReactMqttBundle\Protocols;

use RuntimeException;

class VersionViolation extends RuntimeException
{
    protected $message = "Protocol error";
}