<?php

namespace App\Http\Controllers\Projects;

use App\Data\ProjectData;
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
        // $projects = ProjectData::collect(Project::with('author', 'contact.firm')->paginate(10));
        $projects = Project::with('author', 'contact.firm')->paginate(10);

        return Inertia::render('Projects/Index', [
            'projects' => $projects,
        ]);
    }
}
