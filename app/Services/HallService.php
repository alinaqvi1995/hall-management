<?php
namespace App\Services;

use App\Models\Hall;
use App\Models\Lawn;
use App\Repositories\Hall\HallRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HallService
{
    protected $repo;

    public function __construct(HallRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

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

            // Create hall
            $hall = $this->repo->create($data);

            // Sync lawns
            $this->syncLawns($hall, $lawns);

            return $hall;
        });
    }

    /**
     * Update hall and sync lawns
     */
    public function updateWithLawns(Hall $hall, array $data, array $lawns): Hall
    {
        return DB::transaction(function () use ($hall, $data, $lawns) {

            $data['updated_by'] = Auth::id();
            $this->repo->update($hall, $data);

            // Sync lawns
            $this->syncLawns($hall, $lawns);

            return $hall;
        });
    }

    /**
     * Sync lawns for a hall
     */
    protected function syncLawns(Hall $hall, array $lawns): void
    {
        $existingIds = $hall->lawns()->pluck('id')->toArray();
        $sentIds     = array_filter(array_map(fn($l) => $l['id'] ?? null, $lawns));

        // Delete removed lawns
        $deleteIds = array_diff($existingIds, $sentIds);
        if (! empty($deleteIds)) {
            Lawn::whereIn('id', $deleteIds)->delete();
        }

        foreach ($lawns as $lawnData) {
            if (! empty($lawnData['id'])) {
                // Update existing
                $lawn = Lawn::find($lawnData['id']);
                if ($lawn) {
                    $lawn->update([
                        'name'       => $lawnData['name'],
                        'capacity'   => $lawnData['capacity'],
                        'updated_by' => Auth::id(),
                    ]);
                }
            } else {
                // Create new
                Lawn::create([
                    'hall_id'    => $hall->id,
                    'name'       => $lawnData['name'],
                    'capacity'   => $lawnData['capacity'] ?? null,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);
            }
        }
    }
}
