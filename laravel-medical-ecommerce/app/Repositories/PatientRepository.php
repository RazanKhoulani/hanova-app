<?php

namespace App\Repositories;

use App\Models\Patient;

class PatientRepository
{
    public function getAllPatients($perPage = 15)
    {
        return Patient::latest()->paginate($perPage);
    }

    public function findById($id)
    {
        return Patient::findOrFail($id);
    }

    public function create(array $data)
    {
        return Patient::create($data);
    }

    public function update(Patient $patient, array $data)
    {
        $patient->update($data);
        return $patient;
    }

    public function delete(Patient $patient)
    {
        return $patient->delete();
    }
}
