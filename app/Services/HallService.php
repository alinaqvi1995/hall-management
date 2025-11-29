<?php

namespace App\Services;

use App\Models\Hall;
use App\Repositories\Hall\HallRepositoryInterface;

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
}
