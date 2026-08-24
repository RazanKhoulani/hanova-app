<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Patient;
use App\Models\PatientMedicalFact;
use App\Models\PatientProgressPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PatientController extends Controller
{
    public function index()
    {
        $patients = Patient::with('user')->latest()->paginate(15);
        return view('admin.patients.index', compact('patients'));
    }

    public function show($id)
    {
        $patient = Patient::with([
            'user',
            'progressPhotos' => fn ($query) => $query->with('coupon')->latest(),
            'medicalFacts' => fn ($query) => $query->with('sourceMessage')->latest(),
            'documents' => fn ($query) => $query->with('consultation')->latest(),
            'appointments' => fn ($query) => $query->with('consultation')->latest('date'),
        ])->findOrFail($id);
        return view('admin.patients.show', compact('patient'));
    }

    public function approveProgressPhoto(PatientProgressPhoto $photo)
    {
        if ($photo->status !== 'pending') {
            return back()->with('success', 'Progress photos were already reviewed');
        }

        $coupon = null;

        if ($photo->consent_for_discount && $photo->user_id) {
            $coupon = Coupon::create([
                'code' => $this->makePhotoCouponCode($photo),
                'user_id' => $photo->user_id,
                'discount_type' => 'percentage',
                'discount_value' => $photo->discount_percent,
                'status' => 'active',
                'source' => 'before_after_photos',
                'expires_at' => now()->addMonths(3),
            ]);
        }

        $photo->update([
            'status' => 'approved',
            'coupon_id' => $coupon?->id,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'rejection_reason' => null,
        ]);

        $photo->patient->update([
            'image_before' => $photo->before_image,
            'image_after' => $photo->after_image,
        ]);

        return back()->with('success', 'Progress photos approved successfully');
    }

    public function rejectProgressPhoto(Request $request, PatientProgressPhoto $photo)
    {
        if ($photo->status !== 'pending') {
            return back()->with('success', 'Progress photos were already reviewed');
        }

        $data = $request->validate([
            'rejection_reason' => 'nullable|string|max:1000',
        ]);

        $photo->update([
            'status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'rejection_reason' => $data['rejection_reason'] ?? null,
        ]);

        return back()->with('success', 'Progress photos rejected');
    }

    public function updateMedicalFactStatus(Request $request, PatientMedicalFact $fact)
    {
        $data = $request->validate([
            'status' => 'required|in:suggested,confirmed,ignored',
        ]);

        $fact->update($data);

        return back()->with('success', 'Medical fact status updated');
    }

    private function makePhotoCouponCode(PatientProgressPhoto $photo): string
    {
        do {
            $code = 'PHOTO-' . $photo->user_id . '-' . Str::upper(Str::random(6));
        } while (Coupon::where('code', $code)->exists());

        return $code;
    }
}
