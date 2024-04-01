<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Project;
use Inertia\Inertia;

class Form extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Project $project = null, Contact $contact = null)
    {
      if ($contact) {

        if ($project) {

          $project = $project->contact()->associate($contact);

        } else {

          $project = (new Project())->fill(['contact_id' => $contact->cid]);

        }

      } else {

        $project = new Project();

      }

      return Inertia::render('Projects/Form', [

        'project' => $project,

      ]);
    }
}
