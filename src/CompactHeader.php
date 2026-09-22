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

    /** @var list<string>|null */
    private ?array $actionNames = null;

    private bool $excludesActions = false;

    /** @param list<string> $names */
    public function actions(array $names): self
    {
        return $this->selectActions($names, exclude: false);
    }

    /** @param list<string> $names */
    public function hideActions(array $names): self
    {
        return $this->selectActions($names, exclude: true);
    }

    /** @internal This is an additional presentation filter, never an authorization decision. */
    public function isActionVisible(string $name): bool
    {
        if ($this->actionNames === null) {
            return true;
        }

        $isSelected = in_array($name, $this->actionNames, true);

        return $this->excludesActions ? ! $isSelected : $isSelected;
    }

    /** @param list<string> $names */
    private function selectActions(array $names, bool $exclude): self
    {
        /** @var list<mixed> $namesToValidate */
        $namesToValidate = $names;

        foreach ($namesToValidate as $name) {
            if (! is_string($name) || trim($name) === '') {
                throw new InvalidArgumentException('Compact action names must be non-empty strings. Use native action names, not labels.');
            }
        }

        $this->actionNames = array_values(array_unique($names));
        $this->excludesActions = $exclude;

        return $this;
    }

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

        if (in_array($part, [HeaderPart::Breadcrumbs, HeaderPart::SubNavigation], true)) {
            throw new InvalidArgumentException('Native navigation does not support field selection. Use show() instead.');
        }

        /** @var list<mixed> $fieldsToValidate */
        $fieldsToValidate = $fields;

        foreach ($fieldsToValidate as $field) {
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
