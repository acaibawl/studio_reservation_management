<?php

declare(strict_types=1);

namespace App\Services\Owner;

use App\Exceptions\Owner\Studio\ReservedStudioCantDeleteException;
use App\Exceptions\Owner\Studio\ReservedStudioCantUpdateStartAtException;
use App\Models\Studio;
use Illuminate\Database\Eloquent\Collection;

class StudioService
{
    /**
     * @return Collection<int, Studio>
     */
    public function getAll(): Collection
    {
        return Studio::orderBy('id')->get();
    }

    public function insert(array $attributes): Studio
    {
        return Studio::create($attributes);
    }

    /**
     * @throws ReservedStudioCantUpdateStartAtException
     */
    public function update(Studio $studio, array $attributes): bool
    {
        if ($studio->hasFutureReservation() && $attributes['start_at'] !== $studio->start_at) {
            throw new ReservedStudioCantUpdateStartAtException();
        }

        return $studio->update($attributes);
    }

    /**
     * @throws ReservedStudioCantDeleteException
     */
    public function delete(Studio $studio): void
    {
        if ($studio->hasFutureReservation()) {
            throw new ReservedStudioCantDeleteException();
        }
        $studio->delete();
    }
}
