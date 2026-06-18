<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    public function index(Request $request){
        $validated = $request->validate([
            'status' => ['nullable', Rule::enum(TaskStatus::class)]
        ]);

        $filter = $validated['status'] ?? null;

        $tasks = $request->user()->tasks()
        ->when($filter, fn ($q) => $q->where('status', $filter))
        ->latest()
        ->get();

        return Inertia::render('Tasks/Index',[
            'tasks' => $tasks, 
            'filter' => $filter
        ]);
    }


    public function store(StoreTaskRequest $request){
        $request->user()->tasks()->create($request->validated);

        return back()->with('success', 'Task added');
    }


    public function update(UpdateTaskRequest $request, Task $task){
        $this->authorize('update', $task);

        $task->update($request->validated());

        return back()->with('success', 'Task updated.');

    }

    public function destroy(Request $request, Task $task){

        $this->authorize('delete', $task);

        $task->delete();

        return back()->with('success', 'Task deleted');
    }
}
