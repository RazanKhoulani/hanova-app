<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Services\AppointmentService;
use App\Http\Requests\Appointment\AvailableAppointmentSlotsRequest;
use App\Http\Requests\Appointment\StoreAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    protected AppointmentService $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }

    /**
     * Return available booking slots for a doctor and date.
     */
    public function availableSlots(AvailableAppointmentSlotsRequest $request)
    {
        $filters = $request->validated();
        $lang = $request->query('lang', $request->header('Accept-Language', 'ar'));
        $filters['lang'] = str_starts_with((string) $lang, 'en') ? 'en' : 'ar';

        $slots = $this->appointmentService->getAvailableSlots($filters);

        return response()->json([
            'data' => $slots,
            'message' => $slots['message'] ?? 'Available slots retrieved successfully',
        ]);
    }

    /**
     * Display a listing of appointments.
     */
    public function index()
    {
        $appointments = $this->appointmentService->getUserAppointments(auth()->id());
        return AppointmentResource::collection($appointments);
    }

    /**
     * Store a newly created appointment.
     */
    public function store(StoreAppointmentRequest $request)
    {
        $user = $request->user();
        $patient = Patient::firstOrCreate(
            ['user_id' => $user->id],
            [
                'name' => $user->name,
                'phone' => $user->phone,
            ]
        );

        $payload = $request->validated();
        $payload['patient_id'] = $patient->id;

        $appointment = $this->appointmentService->bookAppointment($payload);
        return new AppointmentResource($appointment);
    }

    /**
     * Display the specified appointment.
     */
    public function show($id)
    {
        $appointment = $this->appointmentService->getAppointmentById($id);
        $this->authorizeAppointmentAccess($appointment, request()->user());

        return new AppointmentResource($appointment);
    }

    /**
     * Update the specified appointment.
     */
    public function update(Request $request, $id)
    {
        $appointment = $this->appointmentService->getAppointmentById($id);
        $user = $request->user();
        $this->authorizeAppointmentAccess($appointment, $user);

        $payload = $this->isStaff($user)
            ? $request->validate([
                'status' => 'required|in:pending,confirmed,completed,cancelled',
            ])
            : $request->validate([
                'status' => 'sometimes|in:cancelled',
                'doctor_id' => 'sometimes|nullable|exists:users,id',
                'date' => 'sometimes|date|after_or_equal:today',
                'time' => 'sometimes|date_format:H:i',
                'type' => 'sometimes|in:online,clinic',
                'appointment_type' => 'sometimes|in:consultation,session,treatment',
                'specialty' => 'sometimes|in:skin,hair,nutrition',
            ]);

        if (! $this->isStaff($user) && in_array($appointment->status, ['completed', 'cancelled'], true)) {
            abort(422, 'Completed or cancelled appointments cannot be changed.');
        }

        $updatedAppointment = $this->appointmentService->updateAppointment($id, $payload);

        return new AppointmentResource($updatedAppointment);
    }

    /**
     * Remove the specified appointment.
     */
    public function destroy($id)
    {
        $appointment = $this->appointmentService->getAppointmentById($id);
        $this->authorizeAppointmentAccess($appointment, request()->user());

        $this->appointmentService->deleteAppointment($id);

        return response()->json(['message' => 'Appointment cancelled successfully'], 204);
    }

    private function authorizeAppointmentAccess($appointment, $user): void
    {
        if ($this->isStaff($user)) {
            return;
        }

        if ((int) $appointment->patient?->user_id !== (int) $user->id) {
            abort(403);
        }
    }

    private function isStaff($user): bool
    {
        return $user?->hasRole('admin') || $user?->hasRole('doctor');
    }
}
