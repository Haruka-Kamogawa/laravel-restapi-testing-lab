<?php

namespace App\Services;

use App\Repositories\TodoRepository;

class TodoService
{
    protected TodoRepository $repo;

    public function __construct(TodoRepository $repo)
    {
        $this->repo = $repo;
    }

    public function list($userid)
    {
        return $this->repo->allForUser($userid);
    }

    public function create($userId, $data)
    {
        return $this->repo->createForUser($userId, $data);
    }
}