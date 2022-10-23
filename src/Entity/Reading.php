<?php

namespace App\Entity;

use App\Repository\ReadingRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use http\Exception\InvalidArgumentException;

/**
 * @ORM\Entity(repositoryClass=ReadingRepository::class)
 */
class Reading
{

    const VALID_TYPES = ['temperature', 'humidity', 'heater', 'connection', 'pressure', 'gas', 'turnOffPi'];
    const DB_UPLOAD_THRESHOLD = [
        'temperature' => 0.175,
        'humidity' => 3,
        'heater' => 1,
        'connection' => 1,
        'pressure' => 2,
        'gas' => 6,
        'turnOffPi' => 1,
    ];

    public static function getDBThresholdForValue(string $value): float
    {
        if (isset(self::DB_UPLOAD_THRESHOLD[$value])) {
            self::DB_UPLOAD_THRESHOLD[$value];
        }
        return 1;
    }

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $time;

    /**
     * @ORM\Column(type="float")
     */
    private $value;

    public function __construct()
    {
        $this->time = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        if ($type != null && !in_array($type, self::VALID_TYPES))
            throw new \InvalidArgumentException("Invalid type '" . $type . "'");
        $this->type = $type;

        return $this;
    }

    public function getTime(): ?DateTimeImmutable
    {
        return $this->time;
    }

    public function setTime(DateTimeImmutable $time): self
    {
        $this->time = $time;

        return $this;
    }

    public function getValue(): ?float
    {
        return $this->value;
    }

    public function setValue(float $value): self
    {
        $this->value = $value;

        return $this;
    }
}
