<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTodoRequest;
use App\Http\Requests\UpdateTodoRequest;
use App\Http\Resources\TodoResource;
use App\Models\Todo;
use App\Repositories\TodoRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TodoController extends Controller
{
    protected TodoRepository $repo;

    public function __construct(TodoRepository $repo)
    {
        $this->repo = $repo;
    }

    // GET /api/todos
    public function index()
    {
        $todos = $this->repo->allForUser(Auth::id());
        return TodoResource::collection($todos);
    }

    // POST /api/todos
    public function store(StoreTodoRequest $request)
    {
        $todo = $this->repo->createForUser(Auth::id(), $request->validated());
        return (new TodoResource($todo))->response()->setStatusCode(201);
    }

    // GET /api/todos/{todo}
    public function show(Todo $todo)
    {
        if ($todo->user_id !== Auth::id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        return new TodoResource($todo);
    }

    // PUT /api/todos/{todo}
    public function update(UpdateTodoRequest $request,Todo $todo)
    {
        if ($todo->user_id !== Auth::id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $this->repo->update($todo, $request->validated());

        return new TodoResource($todo);
    }

    // DELETE /api/todos/{todo}
    public function destroy(Todo $todo)
    {
        if ($todo->user_id !== Auth::id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $this->repo->delete($todo);

        return response()->json(['message' => 'Deleted'], 200);
    }
}
