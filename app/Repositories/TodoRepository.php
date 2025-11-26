<?php

namespace App\Repositories;

use App\Models\Todo;
use Illuminate\Database\Eloquent\Collection;

class TodoRepository
{
    public function allForUser(int $userId): Collection
    {
        return Todo::where('user_id', $userId)->latest()->get();
    }

    public function findForUser(int $userId, int $todoId): ?Todo
    {
        return Todo::where('user_id', $userId)->where('id', $todoId)->first();
    }

    public function createForUser(int $userId, array $data): Todo
    {
        $data['user_id'] = $userId;
        return Todo::create($data);
    }

    public function update(Todo $todo, array $data): Todo
    {
        $todo->update($data);
        return $todo;
    }

    public function delete(Todo $todo): void
    {
        $todo->delete();
    }
}