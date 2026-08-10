<?php

namespace App\Repositories;

use App\Models\Consultation;

class ConsultationRepository
{
    public function getUserConsultations($userId, $perPage = 15)
    {
        return Consultation::where('user_id', $userId)
            ->orWhere('doctor_id', $userId)
            ->latest()->paginate($perPage);
    }

    public function findById($id)
    {
        return Consultation::findOrFail($id);
    }

    public function create(array $data)
    {
        return Consultation::create($data);
    }

    public function update(Consultation $consultation, array $data)
    {
        $consultation->update($data);
        return $consultation;
    }

    public function delete(Consultation $consultation)
    {
        return $consultation->delete();
    }
}
