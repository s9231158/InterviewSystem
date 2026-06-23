<?php

namespace App\Http\Controllers;

use App\Models\PersonalTrait;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
    /**
     * Display Chieh-Hsun's profile including all personal traits.
     */
    public function index(): JsonResponse
    {
        $traits = PersonalTrait::all();

        // Map categories for easier consumption in frontend
        $mappedTraits = $traits->pluck('content', 'category')->toArray();

        return response()->json([
            'profile' => [
                'name' => '柯智勛 (Chieh-Hsun)',
                'title' => 'Laravel Backend Engineer',
                'traits' => $mappedTraits,
            ]
        ]);
    }
}
