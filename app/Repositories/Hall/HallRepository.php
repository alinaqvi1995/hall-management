<?php

namespace App\Repositories\Hall;

use App\Models\Hall;

class HallRepository implements HallRepositoryInterface
{
    public function all()
    {
        return Hall::with('users')->get();
    }

    public function find(int $id): ?Hall
    {
        return Hall::with('users')->find($id);
    }

    public function create(array $data): Hall
    {
        return Hall::create($data);
    }

    public function update(Hall $hall, array $data): Hall
    {
        $hall->update($data);
        return $hall;
    }

    public function delete(Hall $hall): bool
    {
        $hall->users()->delete();
        return $hall->delete();
    }
}
