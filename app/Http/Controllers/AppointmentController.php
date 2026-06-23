<?php

namespace App\Http\Controllers;

use App\Mail\AppointmentNotification;
use App\Models\Interview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AppointmentController extends Controller
{
    /**
     * Store a newly created HR appointment.
     */
    public function store(Request $request): JsonResponse
    {
        // 1. Strict input validation to prevent SQLi/buffer issues
        $validated = $request->validate([
            'hr_name' => 'required|string|max:100',
            'company_name' => 'required|string|max:100',
            'interview_time' => 'required|date|after_or_equal:now',
            'contact_info' => 'required|string|max:150',
        ]);

        try {
            // 2. Save appointment to database via Eloquent parameters
            $interview = Interview::create($validated);

            // 3. Send email notification to Chieh-Hsun
            // The email to send is hardcoded to the requirement specified address: s9231158@gmail.com
            Mail::to('s9231158@gmail.com')->send(new AppointmentNotification($interview));

            return response()->json([
                'success' => true,
                'message' => 'Appointment created and notification sent successfully.',
                'appointment' => [
                    'id' => $interview->id,
                    'hr_name' => $interview->hr_name,
                    'company_name' => $interview->company_name,
                    'interview_time' => $interview->interview_time->toIso8601String(),
                ]
            ], 201);

        } catch (\Exception $e) {
            // Error Handling: Log detailed information locally but return a generic response (Fail Safe)
            Log::error('[Appointment] Failed to process HR appointment.', [
                'error' => $e->getMessage(),
                'input' => $request->except(['contact_info']), // Avoid logging PII details in logs
            ]);

            return response()->json([
                'success' => false,
                'error' => 'An error occurred while processing the appointment. Please try again later.'
            ], 500);
        }
    }
}
