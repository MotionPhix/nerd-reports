<?php

namespace App\Http\Controllers\Tasks;

use App\Http\Controllers\Controller;
use App\Services\TaskManagementService;
use App\Models\Task;
use App\Models\Board;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TaskController extends Controller
{
  use AuthorizesRequests;

  public function __construct(
    protected TaskManagementService $taskService
  ) {}

  /**
   * Display tasks index
   */
  public function index(Request $request)
  {
    $user = Auth::user();

    $filters = $request->only([
      'status', 'priority', 'project_id', 'due_date_from',
      'due_date_to', 'overdue', 'order_by', 'order_direction'
    ]);

    $tasks = $this->taskService->getUserTasks($user, $filters);
    $overdueTasks = $this->taskService->getOverdueTasks($user);
    $taskStats = $this->taskService->getUserTaskStats($user);

    return Inertia::render('Tasks/Index', [
      'tasks' => $tasks,
      'overdueTasks' => $overdueTasks,
      'taskStats' => $taskStats,
      'filters' => $filters,
      'projects' => Project::with('contact.firm')->get(),
      'users' => User::select(['id', 'first_name', 'last_name'])->get(),
    ]);
  }

  /**
   * Show task details
   */
  public function show(Task $task)
  {
    $this->authorize('view', $task);

    $task->load([
      'project.contact.firm',
      'board',
      'user',
      'assignee',
      'comments.user'
    ]);

    return Inertia::render('Tasks/Show', [
      'task' => $task,
      'timeLog' => $task->getFormattedTimeSpent(),
    ]);
  }

  /**
   * Show create task form
   */
  public function create(Request $request)
  {
    return Inertia::render('Tasks/Create', [
      'projects' => Project::with(['boards', 'contact.firm'])->get(),
      'users' => User::select(['id', 'first_name', 'last_name'])->get(),
      'defaultProject' => $request->project_id,
      'defaultBoard' => $request->board_id,
    ]);
  }

  /**
   * Store a new task
   */
  public function store(Request $request)
  {
    $request->validate([
      'name' => 'required|string|max:255',
      'description' => 'nullable|string',
      'board_id' => 'required|exists:boards,uuid',
      'project_id' => 'nullable|exists:projects,uuid',
      'assigned_to' => 'required|exists:users,id',
      'priority' => 'required|in:low,medium,high,urgent',
      'estimated_hours' => 'nullable|numeric|min:0',
      'due_date' => 'nullable|date|after:today',
      'notes' => 'nullable|string',
    ]);

    try {
      $task = $this->taskService->createTask($request->all());

      return redirect()->route('tasks.show', $task)
        ->with('success', 'Task created successfully!');
    } catch (\Exception $e) {
      return back()->withErrors(['error' => 'Failed to create task: ' . $e->getMessage()]);
    }
  }

  /**
   * Show edit task form
   */
  public function edit(Task $task)
  {
    $this->authorize('update', $task);

    $task->load(['project.boards', 'board']);

    return Inertia::render('Tasks/Edit', [
      'task' => $task,
      'projects' => Project::with(['boards', 'contact.firm'])->get(),
      'users' => User::select(['id', 'first_name', 'last_name'])->get(),
    ]);
  }

  /**
   * Update a task
   */
  public function update(Request $request, Task $task)
  {
    $this->authorize('update', $task);

    $request->validate([
      'name' => 'required|string|max:255',
      'description' => 'nullable|string',
      'status' => 'required|in:todo,in_progress,completed,cancelled,on_hold,review',
      'priority' => 'required|in:low,medium,high,urgent',
      'estimated_hours' => 'nullable|numeric|min:0',
      'actual_hours' => 'nullable|numeric|min:0',
      'due_date' => 'nullable|date',
      'notes' => 'nullable|string',
    ]);

    try {
      $task = $this->taskService->updateTask($task, $request->all());

      return redirect()->route('tasks.show', $task)
        ->with('success', 'Task updated successfully!');
    } catch (\Exception $e) {
      return back()->withErrors(['error' => 'Failed to update task: ' . $e->getMessage()]);
    }
  }

  /**
   * Start working on a task
   */
  public function start(Task $task)
  {
    $this->authorize('update', $task);

    try {
      $this->taskService->startTask($task);

      return back()->with('success', 'Task started successfully!');
    } catch (\Exception $e) {
      return back()->withErrors(['error' => $e->getMessage()]);
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

      return back()->with('success', 'Task completed successfully!');
    } catch (\Exception $e) {
      return back()->withErrors(['error' => $e->getMessage()]);
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

      return back()->with('success', 'Time logged successfully!');
    } catch (\Exception $e) {
      return back()->withErrors(['error' => $e->getMessage()]);
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

      return response()->json(['message' => 'Task moved successfully']);
    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage()], 422);
    }
  }

  /**
   * Assign task to user
   */
  public function assign(Request $request, Task $task)
  {
    $this->authorize('update', $task);

    $request->validate([
      'user_id' => 'required|exists:users,id',
    ]);

    try {
      $user = User::findOrFail($request->user_id);
      $this->taskService->assignTask($task, $user);

      return back()->with('success', 'Task assigned successfully!');
    } catch (\Exception $e) {
      return back()->withErrors(['error' => $e->getMessage()]);
    }
  }

  /**
   * Delete a task
   */
  public function destroy(Task $task)
  {
    $this->authorize('delete', $task);

    $task->delete();

    return redirect()->route('tasks.index')
      ->with('success', 'Task deleted successfully!');
  }

  /**
   * Bulk update task positions (for drag and drop)
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

      return response()->json(['message' => 'Task positions updated successfully']);
    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage()], 422);
    }
  }
}
