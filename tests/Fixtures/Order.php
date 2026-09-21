<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPageHeader\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $guarded = [];

    public function notes(): HasMany
    {
        return $this->hasMany(OrderNote::class);
    }

    protected function casts(): array
    {
        return ['status' => OrderStatus::class];
    }
}
