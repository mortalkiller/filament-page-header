<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPageHeader;

use InvalidArgumentException;
use MortalKiller\FilamentPageHeader\Enums\HeaderPart;

final class CompactHeader
{
    /** @var array<string, true> */
    private array $visibleParts = [];

    /** @var array<string, list<string>> */
    private array $fields = [];

    public function show(HeaderPart ...$parts): self
    {
        foreach ($parts as $part) {
            $this->visibleParts[$part->value] = true;
        }

        return $this;
    }

    /** @param list<string> $fields */
    public function only(HeaderPart $part, array $fields): self
    {
        if ($part === HeaderPart::Image) {
            throw new InvalidArgumentException('The image block does not support field selection. Use show(HeaderPart::Image).');
        }

        foreach ($fields as $field) {
            if (! is_string($field) || trim($field) === '') {
                throw new InvalidArgumentException('Compact field names must be non-empty strings.');
            }
        }

        $this->fields[$part->value] = array_values(array_unique($fields));

        return $this->show($part);
    }

    public function isVisible(HeaderPart $part): bool
    {
        return isset($this->visibleParts[$part->value]) && ($this->fields[$part->value] ?? null) !== [];
    }

    /** @return list<string>|null */
    public function getFields(HeaderPart $part): ?array
    {
        return $this->fields[$part->value] ?? null;
    }
}
