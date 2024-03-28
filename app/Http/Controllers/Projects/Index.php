<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Inertia\Inertia;

class Index extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke()
    {
        $projects = Project::paginate(10);

        return Inertia::render('Projects/Index', [
            'projects' => $projects,
        ]);
    }
}
