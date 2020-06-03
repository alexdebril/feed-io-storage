<?php declare(strict_types=1);


namespace FeedIo\Storage\Entity\Feed;

class Status
{
    const PENDING = 'PENDING';

    const APPROVED = 'APPROVED';

    const REJECTED = 'REJECTED';

    const ACCEPTED = 'ACCEPTED';

    private string $value;

    public function __construct(string $value)
    {
        $this->set($value);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->getValue();
    }

    public function is(string $value): bool
    {
        return $value === $this->getValue();
    }

    private function set(string $value): void
    {
        $values = (new \ReflectionClass(self::class))->getConstants();
        if (! in_array($value, $values)) {
            throw new \UnexpectedValueException("{$value} is not a valid status");
        }
        $this->value = $value;
    }
}
