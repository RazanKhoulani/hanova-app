<?php

namespace App\Services;

use App\Repositories\PatientRepository;
use Illuminate\Http\UploadedFile;

class PatientService
{
    protected PatientRepository $patientRepository;

    public function __construct(PatientRepository $patientRepository)
    {
        $this->patientRepository = $patientRepository;
    }

    public function getAllPatients($perPage = 15)
    {
        return $this->patientRepository->getAllPatients($perPage);
    }

    public function getPatientById($id)
    {
        return $this->patientRepository->findById($id);
    }

    public function createPatient(array $data)
    {
        // Handle medical files / image uploads
        if (isset($data['image_before']) && $data['image_before'] instanceof UploadedFile) {
            $data['image_before'] = $data['image_before']->store('patients/before', 'public');
        }
        if (isset($data['image_after']) && $data['image_after'] instanceof UploadedFile) {
            $data['image_after'] = $data['image_after']->store('patients/after', 'public');
        }
        if (isset($data['medical_file']) && $data['medical_file'] instanceof UploadedFile) {
            $data['medical_file'] = $data['medical_file']->store('patients/files', 'public');
        }

        return $this->patientRepository->create($data);
    }

    public function updatePatient($patient, array $data)
    {
        if (isset($data['image_before']) && $data['image_before'] instanceof UploadedFile) {
            $data['image_before'] = $data['image_before']->store('patients/before', 'public');
        }
        if (isset($data['image_after']) && $data['image_after'] instanceof UploadedFile) {
            $data['image_after'] = $data['image_after']->store('patients/after', 'public');
        }
        if (isset($data['medical_file']) && $data['medical_file'] instanceof UploadedFile) {
            $data['medical_file'] = $data['medical_file']->store('patients/files', 'public');
        }

        return $this->patientRepository->update($patient, $data);
    }

    public function deletePatient($patient)
    {
        return $this->patientRepository->delete($patient);
    }
}
