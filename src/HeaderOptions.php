<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPageHeader;

use InvalidArgumentException;
use MortalKiller\FilamentPageHeader\Enums\HeaderMode;

final readonly class HeaderOptions
{
    /** @param array<int, HeaderMode> $breakpoints */
    public function __construct(
        private HeaderMode $mode = HeaderMode::Normal,
        private array $breakpoints = [],
        private ?int $offset = null,
        private ?string $topbarSelector = '.fi-topbar-ctn, .fi-topbar',
        private bool $hideBreadcrumbsWhenCompact = true,
        private ?int $compactBelow = null,
    ) {
        if ($offset !== null && $offset < 0) {
            throw new InvalidArgumentException('The header offset must be zero or positive.');
        }

        if ($compactBelow !== null && $compactBelow < 1) {
            throw new InvalidArgumentException('The compact breakpoint must be positive.');
        }

        /** @var array<array-key, mixed> $breakpointsToValidate */
        $breakpointsToValidate = $breakpoints;

        foreach ($breakpointsToValidate as $width => $mode) {
            if (! is_int($width) || $width < 0 || ! $mode instanceof HeaderMode) {
                throw new InvalidArgumentException('Responsive modes must map non-negative integer widths to HeaderMode cases.');
            }
        }
    }

    public function mode(HeaderMode $mode): self
    {
        return $this->with(['mode' => $mode, 'breakpoints' => [], 'compactBelow' => null]);
    }

    public function compactBelow(int $width): self
    {
        return $this->with(['compactBelow' => $width]);
    }

    /** @param array<int, HeaderMode> $breakpoints */
    public function responsive(array $breakpoints): self
    {
        return $this->with(['breakpoints' => $breakpoints]);
    }

    public function offset(?int $pixels): self
    {
        return $this->with(['offset' => $pixels]);
    }

    public function topbarSelector(?string $selector): self
    {
        return $this->with(['topbarSelector' => $selector]);
    }

    public function hideBreadcrumbsWhenCompact(bool $condition = true): self
    {
        return $this->with(['hideBreadcrumbsWhenCompact' => $condition]);
    }

    /** @return array{mode: string, breakpoints: list<array{minWidth: int, mode: string}>, offset: int|null, topbarSelector: string|null, hideBreadcrumbsWhenCompact: bool, compactBelow: int|null} */
    public function toArray(): array
    {
        $breakpoints = $this->breakpoints;
        ksort($breakpoints, SORT_NUMERIC);
        $responsive = [];

        foreach ($breakpoints as $width => $mode) {
            $responsive[] = ['minWidth' => $width, 'mode' => $mode->value];
        }

        return [
            'mode' => $this->mode->value,
            'breakpoints' => $responsive,
            'offset' => $this->offset,
            'topbarSelector' => $this->topbarSelector,
            'hideBreadcrumbsWhenCompact' => $this->hideBreadcrumbsWhenCompact,
            'compactBelow' => $this->compactBelow,
        ];
    }

    /** @param array<string, mixed> $changes */
    private function with(array $changes): self
    {
        return new self(...array_replace(get_object_vars($this), $changes));
    }
}
