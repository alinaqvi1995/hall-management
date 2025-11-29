<?php

namespace App\Repositories\Hall;

use App\Models\Hall;

interface HallRepositoryInterface
{
    public function all();
    public function create(array $data): Hall;
    public function find(int $id): ?Hall;
    public function update(Hall $hall, array $data): Hall;
    public function delete(Hall $hall): bool;
}
