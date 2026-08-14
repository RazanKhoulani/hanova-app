<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\StorePatientRequest;
use App\Http\Resources\PatientResource;
use App\Models\Patient;
use App\Services\PatientService;
use App\Support\SyrianPhoneNumber;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    protected PatientService $patientService;

    public function __construct(PatientService $patientService)
    {
        $this->patientService = $patientService;
    }

    /**
     * Display a listing of patients.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $patients = $this->isStaff($user)
            ? $this->patientService->getAllPatients()
            : Patient::where('user_id', $user->id)->latest()->paginate(15);

        return PatientResource::collection($patients);
    }

    /**
     * Store a newly created patient profile.
     */
    public function store(StorePatientRequest $request)
    {
        $payload = $request->validated();
        $payload['user_id'] = $request->user()->id;

        $patient = $this->patientService->createPatient($payload);

        return new PatientResource($patient);
    }

    /**
     * Display the specified patient profile.
     */
    public function show($id)
    {
        $patient = $this->patientService->getPatientById($id);
        $this->authorizePatientAccess($patient, request()->user());

        return new PatientResource($patient);
    }

    /**
     * Update the specified patient profile.
     */
    public function update(Request $request, $id)
    {
        $patient = $this->patientService->getPatientById($id);
        $this->authorizePatientAccess($patient, $request->user());

        if ($request->has('phone')) {
            $request->merge([
                'phone' => SyrianPhoneNumber::normalize($request->input('phone')),
            ]);
        }

        $payload = $request->validate([
            'name' => 'sometimes|string|max:255',
            'age' => 'sometimes|integer|min:0',
            'phone' => ['sometimes', 'string', 'regex:'.SyrianPhoneNumber::VALIDATION_REGEX],
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'medical_file' => 'nullable|file|mimes:jpeg,png,jpg,pdf,doc,docx|max:5120',
            'image_before' => 'nullable|image|max:5120',
            'image_after' => 'nullable|image|max:5120',
        ]);

        $updatedPatient = $this->patientService->updatePatient($patient, $payload);

        return new PatientResource($updatedPatient);
    }

    /**
     * Remove the specified patient profile.
     */
    public function destroy($id)
    {
        $patient = $this->patientService->getPatientById($id);
        $this->authorizePatientAccess($patient, request()->user());

        $this->patientService->deletePatient($patient);

        return response()->json(['message' => 'Patient profile deleted successfully'], 204);
    }

    private function authorizePatientAccess(Patient $patient, $user): void
    {
        if ($this->isStaff($user)) {
            return;
        }

        if ((int) $patient->user_id !== (int) $user->id) {
            abort(403);
        }
    }

    private function isStaff($user): bool
    {
        return $user?->hasRole('admin') || $user?->hasRole('doctor');
    }
}
