<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\JsonResponse;

class ProjectController extends Controller
{
    /**
     * Display the specified project by name.
     */
    public function show(string $name): JsonResponse
    {
        // Eloquent ORM is used here which automatically sanitizes parameter inputs to prevent SQL Injection.
        $project = Project::where('name', $name)->first();

        if (!$project) {
            return response()->json(['error' => 'Project not found.'], 404);
        }

        return response()->json([
            'project' => [
                'name' => $project->name,
                'title' => $project->title,
                'tech_stack' => $project->tech_stack,
                'challenge' => $project->challenge,
                'solution' => $project->solution,
                'achievement' => $project->achievement,
            ]
        ]);
    }
}
