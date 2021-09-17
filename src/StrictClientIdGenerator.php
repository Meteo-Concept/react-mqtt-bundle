<?php


namespace MeteoConcept\ReactMqttBundle;


class StrictClientIdGenerator implements ClientIdGeneratorInterface
{
    public function generateId(): string
    {
        // valid for all compliant servers according to the norm:
        // 0 < length < 24
        // characters in set 0-9a-zA-Z
        return uniqid("MCbundle");
    }
}