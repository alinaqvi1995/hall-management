<?php
namespace App\Repositories\Hall;

use App\Models\Hall;
use Illuminate\Support\Facades\Auth;

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
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();
        return Hall::create($data);
    }

    public function update(Hall $hall, array $data): Hall
    {
        $data['updated_by'] = Auth::id();
        $hall->update($data);
        return $hall;
    }

    public function delete(Hall $hall): bool
    {
        $hall->users()->delete();
        return $hall->delete();
    }
}
