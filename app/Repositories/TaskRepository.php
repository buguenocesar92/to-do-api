<?php

namespace App\Repositories;

use App\Models\Task;

class TaskRepository
{
    public function getAll()
    {
        return Task::all();
    }

    public function findById(int $id)
    {
        return Task::findOrFail($id);
    }

    public function create(array $data): Task
    {
        return Task::create($data);
    }

    public function update(int $id, array $data): Task
    {
        $task = Task::findOrFail($id);
        $task->update($data);
        return $task;
    }

    public function delete(int $id): void
    {
        $task = Task::findOrFail($id);
        $task->delete();
    }
}
