<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\PatientProgressPhoto;
use Illuminate\Http\Request;

class PatientProgressPhotoController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'before_image' => 'required|image|max:4096',
            'after_image' => 'required|image|max:4096',
            'consent_for_discount' => 'nullable|boolean',
        ]);

        $user = $request->user();
        $patient = Patient::firstOrCreate(
            ['user_id' => $user->id],
            [
                'name' => $user->name,
                'phone' => $user->phone,
            ]
        );

        $photo = PatientProgressPhoto::create([
            'patient_id' => $patient->id,
            'user_id' => $user->id,
            'before_image' => $request->file('before_image')->store('patient-progress/before', 'public'),
            'after_image' => $request->file('after_image')->store('patient-progress/after', 'public'),
            'status' => 'pending',
            'consent_for_discount' => (bool) ($data['consent_for_discount'] ?? false),
            'discount_percent' => 10,
        ]);

        return response()->json([
            'message' => 'Progress photos submitted for review',
            'data' => [
                'id' => $photo->id,
                'status' => $photo->status,
                'consent_for_discount' => $photo->consent_for_discount,
            ],
        ], 201);
    }
}
