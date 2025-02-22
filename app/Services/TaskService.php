<?php

namespace App\Services;

use App\Repositories\TaskRepository;

class TaskService
{
    private TaskRepository $taskRepo;

    public function __construct(TaskRepository $taskRepo)
    {
        $this->taskRepo = $taskRepo;
    }

    public function getAll()
    {
        return $this->taskRepo->getAll();
    }

    public function findById(int $id)
    {
        return $this->taskRepo->findById($id);
    }

    public function create(array $data)
    {
        return $this->taskRepo->create($data);
    }

    public function update(int $id, array $data)
    {
        return $this->taskRepo->update($id, $data);
    }

    public function delete(int $id): void
    {
        $this->taskRepo->delete($id);
    }
}
