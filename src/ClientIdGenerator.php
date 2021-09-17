<?php


namespace MeteoConcept\ReactMqttBundle;


class ClientIdGenerator implements ClientIdGeneratorInterface
{
    private ?string $defaultPrefix;

    private ?int $defaultLength;

    private ?string $defaultCharacterSet;

    public function __construct(string $defaultPrefix = "", int $defaultLength = 20, string $defaultCharacterSet = "a-zA-Z0-9") {
        $this->defaultPrefix = $defaultPrefix;
        $this->defaultLength = $defaultLength;
        $this->defaultCharacterSet = $defaultCharacterSet;
    }

    public function generateId(?string $prefix = null, ?int $length = null, ?string $characterSet = null)
    {
        if ($prefix === null || $this->defaultPrefix !== null)
            $prefix = $this->defaultPrefix;

        if ($length === null && $this->defaultLength !== null)
            $length = $this->defaultLength;

        if ($characterSet === null && $this->defaultCharacterSet !== null)
            $characterSet = $this->defaultCharacterSet;

        $id = uniqid($prefix);
        if ($characterSet !== null) {
            $id = preg_replace("[^$characterSet]", "", $id);
        }

        if ($length !== null) {
            while (strlen($id) < $length) {
                $id .= uniqid();
                if ($characterSet !== null) {
                    $id = preg_replace("[^$characterSet]", "", $id);
                }
            }
            $id = substr($id, 0, $length);
        }

        return $id;
    }
}