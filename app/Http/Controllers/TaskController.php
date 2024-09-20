<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    // Display tasks
    public function index()
    {
        // Fetch all tasks from the database
        $tasks = Task::all();

        // Pass tasks to the view
        return view('task', compact('tasks'));
    }

    // Store a new task
   public function store(Request $request)
    {
        // Validate the request with unique check
        $validator = \Validator::make($request->all(), [
            'task' => 'required|unique:tasks,task',
        ]);

        // If validation fails, return the error message as a JSON response
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

            $task = Task::create([
            'task' => $request->task,
            'is_completed' => false, // New tasks are pending by default
            'is_deleted' => false     // New tasks are not deleted by default
        ]);

        return response()->json(['message' => 'Task added successfully', 'task' => $task]);

    }


    // Update task status
      public function update(Request $request, Task $task)
    {
        // Toggle the completion status
        $task->is_completed = !$task->is_completed;
        $task->save();

        return response()->json(['message' => 'Task status updated successfully', 'task' => $task]);
    }


    // Delete a task
    public function destroy(Task $task)
    {
        // Mark the task as deleted instead of removing it
        $task->is_deleted = true;
        $task->save();

        return response()->json(['message' => 'Task marked as deleted', 'task' => $task]);
    }

}

