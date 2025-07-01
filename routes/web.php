<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Reports\ReportController;
use App\Http\Controllers\Tasks\TaskController;
use App\Http\Controllers\Projects\ProjectController;
use App\Http\Controllers\Interactions\InteractionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
  return Inertia::render('Welcome', [
    'canLogin' => Route::has('login'),
    'canRegister' => Route::has('register'),
  ]);
});

Route::middleware('auth')->group(function () {

  // dashboard
  Route::get('/dashboard/{type?}', [DashboardController::class, 'index'])->name('dashboard');
  Route::post('/dashboard/clear-cache', [DashboardController::class, 'clearCache'])->name('dashboard.clear-cache');

  // Reports - Enhanced Routes
  Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('index');
    Route::get('/create', [ReportController::class, 'create'])->name('create');
    Route::post('/', [ReportController::class, 'store'])->name('store');
    Route::get('/{report}', [ReportController::class, 'show'])->name('show');
    Route::delete('/{report}', [ReportController::class, 'destroy'])->name('destroy');

    // Report Actions
    Route::post('/generate-weekly', [ReportController::class, 'generateWeekly'])->name('generate-weekly');
    Route::get('/{report}/pdf', [ReportController::class, 'downloadPdf'])->name('download-pdf');
    Route::get('/{report}/preview', [ReportController::class, 'previewPdf'])->name('preview-pdf');
    Route::post('/{report}/send-email', [ReportController::class, 'sendEmail'])->name('send-email');
  });

  // Tasks - Enhanced Routes
  Route::prefix('tasks')->name('tasks.')->group(function () {
    Route::get('/', [TaskController::class, 'index'])->name('index');
    Route::get('/create', [TaskController::class, 'create'])->name('create');
    Route::post('/', [TaskController::class, 'store'])->name('store');
    Route::get('/{task}', [TaskController::class, 'show'])->name('show');
    Route::get('/{task}/edit', [TaskController::class, 'edit'])->name('edit');
    Route::patch('/{task}', [TaskController::class, 'update'])->name('update');
    Route::delete('/{task}', [TaskController::class, 'destroy'])->name('destroy');

    // Task Actions
    Route::post('/{task}/start', [TaskController::class, 'start'])->name('start');
    Route::post('/{task}/complete', [TaskController::class, 'complete'])->name('complete');
    Route::post('/{task}/log-time', [TaskController::class, 'logTime'])->name('log-time');
    Route::post('/{task}/move', [TaskController::class, 'move'])->name('move');
    Route::post('/{task}/assign', [TaskController::class, 'assign'])->name('assign');
    Route::post('/update-positions', [TaskController::class, 'updatePositions'])->name('update-positions');
  });

  // Firm Management Routes
  Route::prefix('firms')->name('firms.')->group(function () {
    // Main CRUD routes
    Route::get('/', [\App\Http\Controllers\Firms\FirmController::class, 'index'])->name('index');
    Route::get('/create', [\App\Http\Controllers\Firms\FirmController::class, 'create'])->name('create');
    Route::post('/', [\App\Http\Controllers\Firms\FirmController::class, 'store'])->name('store');
    Route::get('/s/{uuid}', [\App\Http\Controllers\Firms\FirmController::class, 'show'])->name('show');
    Route::get('/e/{uuid}', [\App\Http\Controllers\Firms\FirmController::class, 'edit'])->name('edit');
    Route::put('/{uuid}', [\App\Http\Controllers\Firms\FirmController::class, 'update'])->name('update');
    Route::patch('/{uuid}', [\App\Http\Controllers\Firms\FirmController::class, 'update'])->name('update.patch');
    Route::delete('/{uuid}', [\App\Http\Controllers\Firms\FirmController::class, 'destroy'])->name('destroy');

    // Bulk operations
    Route::post('/bulk-delete', [\App\Http\Controllers\Firms\FirmController::class, 'bulkDelete'])->name('bulk-delete');

    // Export functionality
    Route::get('/export/csv', [\App\Http\Controllers\Firms\FirmController::class, 'export'])->name('export');

    // Search and autocomplete
    Route::get('/search/autocomplete', [\App\Http\Controllers\Firms\FirmController::class, 'search'])->name('search');

    // Statistics endpoint
    Route::get('/data/stats', [\App\Http\Controllers\Firms\FirmController::class, 'stats'])->name('stats');

    // Soft delete operations (if implemented later)
    Route::post('/{uuid}/restore', [\App\Http\Controllers\Firms\FirmController::class, 'restore'])->name('restore');
    Route::delete('/{uuid}/force', [\App\Http\Controllers\Firms\FirmController::class, 'forceDelete'])->name('force-delete');
  });

  // Projects - Enhanced Routes
  Route::prefix('projects')->name('projects.')->group(function () {
    Route::get('/', [ProjectController::class, 'index'])->name('index');
    Route::get('/create', [ProjectController::class, 'create'])->name('create');
    Route::post('/', [ProjectController::class, 'store'])->name('store');
    Route::get('/s/{project}', [ProjectController::class, 'show'])->name('show');
    Route::get('/e/{project}', [ProjectController::class, 'edit'])->name('edit');
    Route::patch('/{project}', [ProjectController::class, 'update'])->name('update');
    Route::delete('/{project}', [ProjectController::class, 'destroy'])->name('destroy');

    // Project Actions
    Route::post('/{project}/archive', [ProjectController::class, 'archive'])->name('archive');
    Route::get('/{project}/stats', [ProjectController::class, 'stats'])->name('stats');
    Route::get('/{project}/timeline', [ProjectController::class, 'timeline'])->name('timeline');
  });

  // Interactions - New Routes
  Route::prefix('interactions')->name('interactions.')->group(function () {
    Route::get('/', [InteractionController::class, 'index'])->name('index');
    Route::get('/create', [InteractionController::class, 'create'])->name('create');
    Route::post('/', [InteractionController::class, 'store'])->name('store');
    Route::get('/{interaction}', [InteractionController::class, 'show'])->name('show');
    Route::get('/{interaction}/edit', [InteractionController::class, 'edit'])->name('edit');
    Route::patch('/{interaction}', [InteractionController::class, 'update'])->name('update');
    Route::delete('/{interaction}', [InteractionController::class, 'destroy'])->name('destroy');

    // Interaction Actions
    Route::post('/{interaction}/complete-follow-up', [InteractionController::class, 'completeFollowUp'])->name('complete-follow-up');
    Route::post('/{interaction}/schedule-follow-up', [InteractionController::class, 'scheduleFollowUp'])->name('schedule-follow-up');
    Route::get('/contact/{contact}', [InteractionController::class, 'forContact'])->name('for-contact');
    Route::get('/project/{project}', [InteractionController::class, 'forProject'])->name('for-project');
    Route::get('/stats', [InteractionController::class, 'stats'])->name('stats');
  });

  // Existing Routes (keeping your current structure)
  // Contact Management Routes (Updated)
  Route::prefix('contacts')->name('contacts.')->group(function () {
    // Main CRUD routes
    Route::get('/', [\App\Http\Controllers\Contacts\ContactController::class, 'index'])->name('index');
    Route::get('/create', [\App\Http\Controllers\Contacts\ContactController::class, 'create'])->name('create');
    Route::post('/', [\App\Http\Controllers\Contacts\ContactController::class, 'store'])->name('store');
    Route::get('/s/{uuid}', [\App\Http\Controllers\Contacts\ContactController::class, 'show'])->name('show');
    Route::get('/e/{uuid}', [\App\Http\Controllers\Contacts\ContactController::class, 'edit'])->name('edit');
    Route::put('/{uuid}', [\App\Http\Controllers\Contacts\ContactController::class, 'update'])->name('update');
    Route::patch('/{uuid}', [\App\Http\Controllers\Contacts\ContactController::class, 'update'])->name('update.patch');
    Route::delete('/{uuid}', [\App\Http\Controllers\Contacts\ContactController::class, 'destroy'])->name('destroy');

    // Search and filtering
    Route::get('/search/autocomplete', [\App\Http\Controllers\Contacts\ContactController::class, 'search'])->name('search');
    Route::get('/firm/{firmId}/contacts', [\App\Http\Controllers\Contacts\ContactController::class, 'getByFirm'])->name('by-firm');

    // Bulk operations
    Route::post('/bulk-delete', [\App\Http\Controllers\Contacts\ContactController::class, 'bulkDelete'])->name('bulk-delete');
    Route::post('/{uuid}/duplicate', [\App\Http\Controllers\Contacts\ContactController::class, 'duplicate'])->name('duplicate');

    // Import/Export functionality
    Route::get('/export/csv', [\App\Http\Controllers\Contacts\ContactController::class, 'export'])->name('export');
    Route::get('/import/form', [\App\Http\Controllers\Contacts\ContactController::class, 'showImport'])->name('import.form');
    Route::post('/import', [\App\Http\Controllers\Contacts\ContactController::class, 'import'])->name('import');

    // Email management
    Route::post('/{uuid}/emails', [\App\Http\Controllers\Contacts\ContactController::class, 'addEmail'])->name('emails.add');
    Route::delete('/{uuid}/emails/{emailId}', [\App\Http\Controllers\Contacts\ContactController::class, 'removeEmail'])->name('emails.remove');
    Route::patch('/{uuid}/emails/{emailId}/primary', [\App\Http\Controllers\Contacts\ContactController::class, 'setPrimaryEmail'])->name('emails.set-primary');

    // Phone management
    Route::post('/{uuid}/phones', [\App\Http\Controllers\Contacts\ContactController::class, 'addPhone'])->name('phones.add');
    Route::delete('/{uuid}/phones/{phoneId}', [\App\Http\Controllers\Contacts\ContactController::class, 'removePhone'])->name('phones.remove');
    Route::patch('/{uuid}/phones/{phoneId}/primary', [\App\Http\Controllers\Contacts\ContactController::class, 'setPrimaryPhone'])->name('phones.set-primary');

    // Avatar management
    Route::post('/{uuid}/avatar', [\App\Http\Controllers\Contacts\ContactController::class, 'uploadAvatar'])->name('avatar.upload');
    Route::delete('/{uuid}/avatar', [\App\Http\Controllers\Contacts\ContactController::class, 'removeAvatar'])->name('avatar.remove');

    // Advanced operations
    Route::post('/merge', [\App\Http\Controllers\Contacts\ContactController::class, 'merge'])->name('merge');
    Route::put('/{uuid}/restore', [\App\Http\Controllers\Contacts\ContactController::class, 'restore'])->name('restore');
    Route::delete('/{uuid}/force', [\App\Http\Controllers\Contacts\ContactController::class, 'forceDelete'])->name('force-delete');

    // Statistics and analytics
    Route::get('/data/stats', [\App\Http\Controllers\Contacts\ContactController::class, 'stats'])->name('stats');
    Route::get('/data/recent', [\App\Http\Controllers\Contacts\ContactController::class, 'recent'])->name('recent');

    // Special views
    Route::get('/management/orphaned', [\App\Http\Controllers\Contacts\ContactController::class, 'orphaned'])->name('orphaned');
    Route::get('/management/duplicates', [\App\Http\Controllers\Contacts\ContactController::class, 'duplicates'])->name('duplicates');
  });

  Route::prefix('comments')->group(function () {
    Route::post('/{task}', \App\Http\Controllers\Comments\Store::class)->name('comments.store');
    Route::patch('/u/{comment}', \App\Http\Controllers\Comments\Update::class)->name('comments.update');
    Route::delete('/d/{comment}', \App\Http\Controllers\Comments\Destroy::class)->name('comments.destroy');
  });

  // Profile
  Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
  Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
  Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
