<?php

namespace App\Services;

use App\Repositories\ConsultationRepository;

class ConsultationService
{
    protected ConsultationRepository $consultationRepository;

    public function __construct(ConsultationRepository $consultationRepository)
    {
        $this->consultationRepository = $consultationRepository;
    }

    public function getUserConsultations($userId)
    {
        return $this->consultationRepository->getUserConsultations($userId);
    }

    public function getConsultationById($id)
    {
        return $this->consultationRepository->findById($id);
    }

    public function requestConsultation($userId, array $data)
    {
        $data['user_id'] = $userId;
        $data['status'] = 'pending';
        return $this->consultationRepository->create($data);
    }

    public function updateConsultationStatus($id, $status)
    {
        $consultation = $this->consultationRepository->findById($id);
        return $this->consultationRepository->update($consultation, ['status' => $status]);
    }
}
