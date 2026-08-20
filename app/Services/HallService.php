<?php

namespace App\Services;

use App\Models\Hall;
use App\Models\Lawn;
use App\Repositories\Hall\HallRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class HallService
{
    public function __construct(protected HallRepositoryInterface $repo) {}

    public function list()
    {
        return $this->repo->all();
    }

    public function find(int $id): ?Hall
    {
        return $this->repo->find($id);
    }

    public function create(array $data): Hall
    {
        return $this->repo->create($data);
    }

    public function update(Hall $hall, array $data): Hall
    {
        return $this->repo->update($hall, $data);
    }

    public function delete(Hall $hall): bool
    {
        return $this->repo->delete($hall);
    }

    public function createWithLawns(array $data, array $lawns): Hall
    {
        return DB::transaction(function () use ($data, $lawns) {
            $data['created_by'] = Auth::id();
            $data['updated_by'] = Auth::id();
            $data = $this->denormaliseLocation($data);

            $hall = $this->repo->create($data);
            $this->syncLawns($hall, $lawns);

            return $hall;
        });
    }

    public function updateWithLawns(Hall $hall, array $data, array $lawns): Hall
    {
        return DB::transaction(function () use ($hall, $data, $lawns) {
            $data['updated_by'] = Auth::id();
            $data = $this->denormaliseLocation($data);

            $this->repo->update($hall, $data);
            $this->syncLawns($hall, $lawns);

            return $hall->refresh();
        });
    }

    /**
     * Store an uploaded logo on the public disk and return its relative path.
     * Replaces the old approach of moving the raw upload into public/ with a
     * caller-supplied filename.
     */
    public function storeLogo(?UploadedFile $file, ?string $existingPath = null): ?string
    {
        if (! $file) {
            return $existingPath;
        }

        // Randomised name: the original filename is attacker-controlled.
        $name = Str::random(24).'.'.$file->extension();
        $path = $file->storeAs('hall-logos', $name, 'public');

        if ($existingPath) {
            $this->deleteLogo($existingPath);
        }

        return 'storage/'.$path;
    }

    public function deleteLogo(?string $path): void
    {
        if (! $path) {
            return;
        }

        // Only ever delete inside the managed logo directory.
        $relative = Str::after($path, 'storage/');

        if (Str::startsWith($relative, 'hall-logos/')) {
            Storage::disk('public')->delete($relative);
        }
    }

    /**
     * Keep the legacy free-text city/state columns in step with the normalised
     * ids, so existing reads and the invoice template keep working.
     */
    private function denormaliseLocation(array $data): array
    {
        if (! empty($data['state_id'])) {
            $data['state'] = \App\Models\State::find($data['state_id'])?->name;
        }

        if (! empty($data['city_id'])) {
            $data['city'] = \App\Models\City::find($data['city_id'])?->name;
        }

        $data['country'] = $data['country'] ?? 'Pakistan';

        return $data;
    }

    /**
     * Create, update and remove lawns to match what the form submitted.
     *
     * A lawn that still holds upcoming bookings is never deleted, because that
     * would orphan those events (lawn_id is nullOnDelete).
     */
    protected function syncLawns(Hall $hall, array $lawns): void
    {
        $existingIds = $hall->lawns()->pluck('id')->all();
        $submittedIds = array_values(array_filter(array_map(
            fn ($l) => isset($l['id']) ? (int) $l['id'] : null,
            $lawns
        )));

        $removeIds = array_diff($existingIds, $submittedIds);

        if ($removeIds) {
            $blocked = Lawn::whereIn('id', $removeIds)
                ->whereHas('bookings', fn ($q) => $q->active()->where('end_datetime', '>=', now()))
                ->pluck('name');

            if ($blocked->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'lawns' => 'Cannot remove '.$blocked->join(', ')
                        .' — there are still upcoming bookings on it. Cancel those first.',
                ]);
            }

            Lawn::whereIn('id', $removeIds)->delete();
        }

        foreach ($lawns as $row) {
            if (empty($row['name'])) {
                continue;
            }

            $payload = [
                'hall_id' => $hall->id,
                'name' => $row['name'],
                'capacity' => $row['capacity'] ?? null,
            ];

            if (! empty($row['id'])) {
                // Scope to this hall so a crafted id cannot edit another venue's lawn.
                Lawn::where('id', $row['id'])->where('hall_id', $hall->id)->first()?->update($payload);
            } else {
                Lawn::create($payload);
            }
        }
    }
}
