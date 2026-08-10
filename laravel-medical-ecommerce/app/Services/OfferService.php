<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Offer;
use App\Models\Order;
use App\Models\Patient;
use App\Models\User;

class OfferService
{
    public function getActiveForUser(?User $user): ?Offer
    {
        return Offer::running()
            ->orderByDesc('priority')
            ->latest()
            ->get()
            ->first(fn (Offer $offer) => $this->matchesSegment($offer->target_segment, $user));
    }

    private function matchesSegment(string $segment, ?User $user): bool
    {
        if ($segment === 'all') {
            return true;
        }

        if (!$user) {
            return false;
        }

        return match ($segment) {
            'new_user' => !Order::where('user_id', $user->id)->exists(),
            'loyal_patient' => Order::where('user_id', $user->id)
                ->whereIn('status', ['paid', 'delivered'])
                ->count() >= 3,
            'before_after_uploaded' => Patient::where('user_id', $user->id)
                ->whereNotNull('image_before')
                ->whereNotNull('image_after')
                ->exists(),
            'has_completed_appointment' => Appointment::whereIn(
                'patient_id',
                Patient::where('user_id', $user->id)->select('id')
            )->where('status', 'completed')->exists(),
            default => false,
        };
    }
}
