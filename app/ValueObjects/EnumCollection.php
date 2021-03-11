<?php

declare(strict_types=1);

namespace App\ValueObjects;

use App\Casts\CastableFromArrayInterface;
use App\Casts\CastableToArrayInterface;
use App\Utils\Assert;
use Iterator;

abstract class EnumCollection implements CastableToArrayInterface, CastableFromArrayInterface, HasValidationRulesInterface, Iterator
{
    private $class;
    /** @var Enum[] */
    private $collection = [];
    private $index = 0;

    public function __construct(string $class)
    {
        Assert::true(is_subclass_of($class, Enum::class));
        $this->class = $class;
    }

    public function has(Enum $enum): bool
    {
        foreach ($this->collection as $item) {
            if ($item->equals($enum)) {
                return true;
            }
        }

        return false;
    }

    public function add(Enum $enum): void
    {
        Assert::isInstanceOf($enum, $this->class);

        $this->collection[] = $enum;
    }

    public function remove(Enum $enum): void
    {
        Assert::isInstanceOf($enum, $this->class);

        foreach ($this->collection as $key => $item) {
            if ($item->equals($enum)) {
                unset($this->collection[$key]);
            }
        }
    }

    public function toArray(): array
    {
        return array_map(function (Enum $e) {
            return $e->toString();
        }, $this->collection);
    }

    public function current()
    {
        return $this->collection[$this->index];
    }

    public function next(): void
    {
        $this->index ++;
    }

    public function key(): int
    {
        return $this->index;
    }

    public function valid(): bool
    {
        return isset($this->collection[$this->key()]);
    }

    public function rewind(): void
    {
        $this->index = 0;
    }

    public function reverse(): void
    {
        $this->collection = array_reverse($this->collection);
        $this->rewind();
    }
}
