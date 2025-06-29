<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TaskManagementService;
use App\Models\Task;
use App\Models\Board;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TasksController extends Controller
{
    public function __construct(
        protected TaskManagementService $taskService
    ) {}

    /**
     * Get tasks with filters
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $filters = $request->only([
            'status', 'priority', 'project_id', 'due_date_from',
            'due_date_to', 'overdue', 'order_by', 'order_direction'
        ]);

        $tasks = $this->taskService->getUserTasks($user, $filters);

        return response()->json($tasks);
    }

    /**
     * Get task statistics
     */
    public function stats(Request $request)
    {
        $user = Auth::user();
        $startDate = $request->start_date ? \Carbon\Carbon::parse($request->start_date) : null;
        $endDate = $request->end_date ? \Carbon\Carbon::parse($request->end_date) : null;

        $stats = $this->taskService->getUserTaskStats($user, $startDate, $endDate);

        return response()->json($stats);
    }

    /**
     * Start a task
     */
    public function start(Task $task)
    {
        $this->authorize('update', $task);

        try {
            $this->taskService->startTask($task);

            return response()->json([
                'success' => true,
                'task' => $task->fresh(),
                'message' => 'Task started successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Complete a task
     */
    public function complete(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $request->validate([
            'actual_hours' => 'nullable|numeric|min:0',
        ]);

        try {
            $this->taskService->completeTask($task, $request->actual_hours);

            return response()->json([
                'success' => true,
                'task' => $task->fresh(),
                'message' => 'Task completed successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Log time for a task
     */
    public function logTime(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $request->validate([
            'hours' => 'required|numeric|min:0.1|max:24',
            'description' => 'nullable|string|max:255',
        ]);

        try {
            $this->taskService->logTime($task, $request->hours, $request->description);

            return response()->json([
                'success' => true,
                'task' => $task->fresh(),
                'message' => 'Time logged successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Move task to different board
     */
    public function move(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $request->validate([
            'board_id' => 'required|exists:boards,uuid',
            'position' => 'nullable|numeric',
        ]);

        try {
            $board = Board::findOrFail($request->board_id);
            $this->taskService->moveTask($task, $board, $request->position);

            return response()->json([
                'success' => true,
                'task' => $task->fresh(),
                'message' => 'Task moved successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Update task status
     */
    public function updateStatus(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $request->validate([
            'status' => 'required|in:todo,in_progress,completed,cancelled,on_hold,review',
        ]);

        try {
            $this->taskService->updateTask($task, ['status' => $request->status]);

            return response()->json([
                'success' => true,
                'task' => $task->fresh(),
                'message' => 'Task status updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Bulk update task positions
     */
    public function updatePositions(Request $request)
    {
        $request->validate([
            'tasks' => 'required|array',
            'tasks.*.id' => 'required|exists:tasks,uuid',
            'tasks.*.position' => 'required|numeric',
            'tasks.*.board_id' => 'nullable|exists:boards,uuid',
        ]);

        try {
            $this->taskService->updateTaskPositions($request->tasks);

            return response()->json([
                'success' => true,
                'message' => 'Task positions updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get overdue tasks
     */
    public function overdue(Request $request)
    {
        $user = Auth::user();
        $overdueTasks = $this->taskService->getOverdueTasks($user);

        return response()->json($overdueTasks);
    }
}
