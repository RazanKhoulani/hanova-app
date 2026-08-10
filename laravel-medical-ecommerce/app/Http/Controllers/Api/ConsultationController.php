<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ConsultationService;
use App\Http\Requests\Consultation\StoreConsultationRequest;
use App\Http\Resources\ConsultationResource;
use App\Models\Consultation;

class ConsultationController extends Controller
{
    protected ConsultationService $consultationService;

    public function __construct(ConsultationService $consultationService)
    {
        $this->consultationService = $consultationService;
    }

    /**
     * List user consultations.
     */
    public function index()
    {
        $consultations = $this->consultationService->getUserConsultations(auth()->id());
        return ConsultationResource::collection($consultations);
    }

    /**
     * Request a new consultation.
     */
    public function store(StoreConsultationRequest $request)
    {
        $consultation = $this->consultationService->requestConsultation(auth()->id(), $request->validated());
        return new ConsultationResource($consultation);
    }

    /**
     * Show consultation details.
     */
    public function show($id)
    {
        $consultation = $this->consultationService->getConsultationById($id);
        $this->authorizeConsultationAccess($consultation, request()->user());

        return new ConsultationResource($consultation);
    }

    private function authorizeConsultationAccess(Consultation $consultation, $user): void
    {
        if ($user?->hasRole('admin') || $user?->hasRole('doctor')) {
            return;
        }

        if ((int) $consultation->user_id !== (int) $user->id) {
            abort(403);
        }
    }
}
