<?php

namespace App\Repositories;

use App\Models\Appointment;

class AppointmentRepository
{
    public function getUserAppointments($userId, $perPage = 15)
    {
        return Appointment::whereHas('patient', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
        ->with(['patient', 'doctor'])
        ->latest()->paginate($perPage);
    }

    public function getAllAppointments($perPage = 15)
    {
        return Appointment::with(['patient', 'doctor'])->latest()->paginate($perPage);
    }

    public function findById($id)
    {
        return Appointment::with(['patient', 'doctor'])->findOrFail($id);
    }

    public function create(array $data)
    {
        return Appointment::create($data);
    }

    public function update(Appointment $appointment, array $data)
    {
        $appointment->update($data);
        return $appointment;
    }

    public function delete(Appointment $appointment)
    {
        return $appointment->delete();
    }
}
