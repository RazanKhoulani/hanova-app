<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\User;
use App\Repositories\AppointmentRepository;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class AppointmentService
{
    private const WEEKLY_HOLIDAY_DAY_KEYS = ['friday'];

    private const DEFAULT_SLOTS = [
        'clinic' => [
            'default' => [
                ['start' => '09:00', 'end' => '12:00'],
                ['start' => '14:00', 'end' => '17:00'],
            ],
        ],
        'online' => [
            'default' => [
                ['start' => '10:00', 'end' => '13:00'],
                ['start' => '15:00', 'end' => '18:00'],
            ],
        ],
        'duration_minutes' => [
            'consultation' => 30,
            'session' => 60,
            'treatment' => 30,
        ],
    ];

    protected AppointmentRepository $appointmentRepository;

    public function __construct(AppointmentRepository $appointmentRepository)
    {
        $this->appointmentRepository = $appointmentRepository;
    }

    public function getUserAppointments($userId)
    {
        return $this->appointmentRepository->getUserAppointments($userId);
    }

    public function getAllAppointments()
    {
        return $this->appointmentRepository->getAllAppointments();
    }

    public function getAppointmentById($id)
    {
        return $this->appointmentRepository->findById($id);
    }

    public function bookAppointment(array $data)
    {
        $data['status'] = 'pending';
        $data['appointment_type'] = $this->normalizeAppointmentType($data['appointment_type'] ?? null);

        if (!empty($data['patient_id']) && $this->patientHasActiveAppointment((int) $data['patient_id'])) {
            throw ValidationException::withMessages([
                'appointment' => 'You already have an active appointment.',
            ]);
        }

        $doctor = $this->resolveDoctor($data['doctor_id'] ?? null);
        $schedule = $this->resolveSchedule($doctor);
        $durationMinutes = $this->resolveDurationMinutes($schedule, $data['appointment_type']);
        $appointmentDate = !empty($data['date'])
            ? Carbon::parse((string) $data['date'])->startOfDay()
            : null;

        if ($appointmentDate && $this->isWeeklyHoliday($appointmentDate)) {
            throw ValidationException::withMessages([
                'date' => $this->weeklyHolidayMessage('ar'),
            ]);
        }

        if (
            empty($data['date'])
            || empty($data['time'])
            || !$this->isTimeAvailable(
                $doctor,
                (string) $data['date'],
                (string) $data['time'],
                (string) ($data['type'] ?? 'clinic'),
                $durationMinutes
            )
        ) {
            throw ValidationException::withMessages([
                'time' => 'Selected time is no longer available.',
            ]);
        }

        if (Schema::hasColumn('appointments', 'doctor_id')) {
            $data['doctor_id'] = $doctor->id;
        } else {
            unset($data['doctor_id']);
        }

        if (Schema::hasColumn('appointments', 'duration_minutes')) {
            $data['duration_minutes'] = $durationMinutes;
        } else {
            unset($data['duration_minutes']);
        }

        if (!Schema::hasColumn('appointments', 'appointment_type')) {
            unset($data['appointment_type']);
        }

        $appointment = $this->appointmentRepository->create($data);

        if (($data['appointment_type'] ?? null) === 'consultation') {
            $appointment->loadMissing('patient');
            Consultation::firstOrCreate(
                ['appointment_id' => $appointment->id],
                [
                    'user_id' => $appointment->patient->user_id,
                    'patient_id' => $appointment->patient_id,
                    'doctor_id' => $appointment->doctor_id,
                    'type' => 'pre_booked',
                    'status' => 'pending',
                    'notes' => $appointment->type === 'online'
                        ? 'Online consultation booked from the mobile application.'
                        : 'Clinic consultation booked from the mobile application.',
                ]
            );
        }

        return $appointment->load('consultation');
    }

    public function getAvailableSlots(array $filters): array
    {
        $dateString = $filters['date'] ?? null;
        if (!$dateString) {
            throw new RuntimeException('Date is required.');
        }

        $sessionType = strtolower($filters['type'] ?? 'clinic');
        if (!in_array($sessionType, ['clinic', 'online'], true)) {
            $sessionType = 'clinic';
        }

        $appointmentType = $this->normalizeAppointmentType($filters['appointment_type'] ?? null);
        $lang = str_starts_with((string) ($filters['lang'] ?? 'ar'), 'en') ? 'en' : 'ar';
        $date = Carbon::parse($dateString)->startOfDay();
        $doctor = $this->resolveDoctor($filters['doctor_id'] ?? null);
        $schedule = $this->resolveSchedule($doctor);
        $durationMinutes = $this->resolveDurationMinutes($schedule, $appointmentType);

        if ($this->isWeeklyHoliday($date)) {
            return $this->closedSlotsResponse(
                doctor: $doctor,
                date: $date,
                sessionType: $sessionType,
                appointmentType: $appointmentType,
                durationMinutes: $durationMinutes,
                reason: 'weekly_holiday',
                message: $this->weeklyHolidayMessage($lang),
            );
        }

        $windows = $this->resolveWindows($schedule, $sessionType, $date);
        $bookedAppointments = $this->getBookedAppointments($date->toDateString(), $doctor->id);

        return [
            'doctor_id' => $doctor->id,
            'doctor_name' => $doctor->name,
            'date' => $date->toDateString(),
            'type' => $sessionType,
            'appointment_type' => $appointmentType,
            'duration_minutes' => $durationMinutes,
            'is_closed' => false,
            'unavailable_reason' => null,
            'message' => null,
            'slots' => $this->generateSlots($date, $windows, $durationMinutes, $bookedAppointments),
        ];
    }

    public function updateAppointment($id, array $data)
    {
        $appointment = $this->appointmentRepository->findById($id);

        $isRescheduling = isset($data['date']) || isset($data['time']) || isset($data['type']) || isset($data['appointment_type']);
        if ($isRescheduling) {
            $date = (string) ($data['date'] ?? $appointment->date);
            $time = (string) ($data['time'] ?? substr((string) $appointment->time, 0, 5));
            $sessionType = (string) ($data['type'] ?? $appointment->type);
            $appointmentType = $this->normalizeAppointmentType($data['appointment_type'] ?? $appointment->appointment_type);
            $doctor = $this->resolveDoctor($data['doctor_id'] ?? $appointment->doctor_id);
            $schedule = $this->resolveSchedule($doctor);
            $duration = $this->resolveDurationMinutes($schedule, $appointmentType);

            if (! $this->isTimeAvailable($doctor, $date, $time, $sessionType, $duration, $appointment->id)) {
                throw ValidationException::withMessages(['time' => 'Selected time is no longer available.']);
            }

            $data['doctor_id'] = $doctor->id;
            $data['appointment_type'] = $appointmentType;
            $data['duration_minutes'] = $duration;
            $data['status'] = 'pending';
        }

        return $this->appointmentRepository->update($appointment, $data);
    }

    public function deleteAppointment($id)
    {
        $appointment = $this->appointmentRepository->findById($id);

        return $this->appointmentRepository->delete($appointment);
    }

    private function patientHasActiveAppointment(int $patientId): bool
    {
        return Appointment::query()
            ->where('patient_id', $patientId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->get()
            ->contains(function (Appointment $appointment) {
                if (!$appointment->date || !$appointment->time) {
                    return false;
                }

                $start = Carbon::parse(sprintf('%s %s', $appointment->date, $appointment->time));
                $duration = $this->durationForAppointment($appointment, 30);

                return $start->copy()->addMinutes($duration)->isFuture();
            });
    }

    private function resolveDoctor(?int $doctorId = null): User
    {
        if ($doctorId) {
            $doctor = User::find($doctorId);
            if ($doctor) {
                return $doctor;
            }
        }

        $doctorWithSchedule = Schema::hasColumn('users', 'availability_schedule')
            ? User::query()->whereNotNull('availability_schedule')->latest()->first()
            : null;

        if ($doctorWithSchedule) {
            return $doctorWithSchedule;
        }

        return User::role('doctor')->latest()->first()
            ?? User::role('admin')->latest()->first()
            ?? User::latest()->first()
            ?? throw new RuntimeException('No doctor account is available.');
    }

    private function isTimeAvailable(
        User $doctor,
        string $dateString,
        string $timeString,
        string $sessionType,
        int $durationMinutes,
        ?int $ignoreAppointmentId = null
    ): bool {
        $date = Carbon::parse($dateString)->startOfDay();
        if ($this->isWeeklyHoliday($date)) {
            return false;
        }

        $schedule = $this->resolveSchedule($doctor);
        $windows = $this->resolveWindows($schedule, $sessionType, $date);

        if (empty($windows)) {
            return false;
        }

        $requestedStart = Carbon::parse(sprintf('%s %s', $date->toDateString(), $timeString));
        $requestedEnd = $requestedStart->copy()->addMinutes($durationMinutes);

        if ($requestedStart->lte(now())) {
            return false;
        }

        $bookedAppointments = $this->getBookedAppointments($date->toDateString(), $doctor->id);
        if ($ignoreAppointmentId) {
            $bookedAppointments = $bookedAppointments->where('id', '!=', $ignoreAppointmentId);
        }

        foreach ($windows as $window) {
            $windowStart = Carbon::parse(sprintf('%s %s', $date->toDateString(), $window['start']));
            $windowEnd = Carbon::parse(sprintf('%s %s', $date->toDateString(), $window['end']));

            if ($requestedStart->gte($windowStart) && $requestedEnd->lte($windowEnd)) {
                return !$this->isSlotBooked($requestedStart, $requestedEnd, $bookedAppointments, $durationMinutes);
            }
        }

        return false;
    }

    private function resolveSchedule(User $doctor): array
    {
        $schedule = is_array($doctor->availability_schedule ?? null)
            ? $doctor->availability_schedule
            : [];

        return $schedule ?: self::DEFAULT_SLOTS;
    }

    private function resolveWindows(array $schedule, string $sessionType, Carbon $date): array
    {
        $dayKey = strtolower($date->format('l'));

        $daySpecificWindows = data_get($schedule, "{$sessionType}.{$dayKey}");
        if (is_array($daySpecificWindows)) {
            return $this->normalizeWindows($daySpecificWindows);
        }

        $candidates = [
            data_get($schedule, "{$sessionType}.default"),
            data_get($schedule, 'default'),
            data_get(self::DEFAULT_SLOTS, "{$sessionType}.{$dayKey}"),
            data_get(self::DEFAULT_SLOTS, "{$sessionType}.default"),
            data_get(self::DEFAULT_SLOTS, 'default'),
        ];

        foreach ($candidates as $candidate) {
            if (is_array($candidate) && !empty($candidate)) {
                return $this->normalizeWindows($candidate);
            }
        }

        return [];
    }

    private function normalizeWindows(array $windows): array
    {
        return array_values(array_filter($windows, static function ($window) {
            return is_array($window)
                && isset($window['start'], $window['end'])
                && $window['start'] !== ''
                && $window['end'] !== '';
        }));
    }

    private function isWeeklyHoliday(Carbon $date): bool
    {
        return in_array(strtolower($date->format('l')), self::WEEKLY_HOLIDAY_DAY_KEYS, true);
    }

    private function weeklyHolidayMessage(string $lang): string
    {
        return $lang === 'en'
            ? 'Friday is a clinic holiday, so there are no appointments available. Please choose another day.'
            : 'يوم الجمعة عطلة في العيادة، لا توجد مواعيد متاحة. اختاري يوماً آخر.';
    }

    private function closedSlotsResponse(
        User $doctor,
        Carbon $date,
        string $sessionType,
        string $appointmentType,
        int $durationMinutes,
        string $reason,
        string $message
    ): array {
        return [
            'doctor_id' => $doctor->id,
            'doctor_name' => $doctor->name,
            'date' => $date->toDateString(),
            'type' => $sessionType,
            'appointment_type' => $appointmentType,
            'duration_minutes' => $durationMinutes,
            'is_closed' => true,
            'unavailable_reason' => $reason,
            'message' => $message,
            'slots' => [],
        ];
    }

    private function getBookedAppointments(string $date, int $doctorId): Collection
    {
        $query = Appointment::query()
            ->whereDate('date', $date)
            ->whereNotIn('status', ['cancelled', 'completed']);

        if (Schema::hasColumn('appointments', 'doctor_id')) {
            $query->where(function ($builder) use ($doctorId) {
                $builder->where('doctor_id', $doctorId)
                    ->orWhereNull('doctor_id');
            });
        }

        return $query->get();
    }

    private function generateSlots(Carbon $date, array $windows, int $durationMinutes, Collection $bookedAppointments): array
    {
        $slots = [];

        foreach ($windows as $window) {
            $windowStart = Carbon::parse(sprintf('%s %s', $date->toDateString(), $window['start']));
            $windowEnd = Carbon::parse(sprintf('%s %s', $date->toDateString(), $window['end']));

            for ($cursor = $windowStart->copy(); $cursor->copy()->addMinutes($durationMinutes)->lte($windowEnd); $cursor->addMinutes($durationMinutes)) {
                $slotEnd = $cursor->copy()->addMinutes($durationMinutes);

                if ($cursor->lte(now())) {
                    continue;
                }

                if ($this->isSlotBooked($cursor, $slotEnd, $bookedAppointments, $durationMinutes)) {
                    continue;
                }

                $slots[$cursor->format('H:i')] = $cursor->format('h:i A');
            }
        }

        return array_values($slots);
    }

    private function isSlotBooked(Carbon $slotStart, Carbon $slotEnd, Collection $bookedAppointments, int $fallbackDurationMinutes): bool
    {
        foreach ($bookedAppointments as $appointment) {
            if (!$appointment->date || !$appointment->time) {
                continue;
            }

            $appointmentStart = Carbon::parse(sprintf('%s %s', $appointment->date, $appointment->time));
            $appointmentEnd = $appointmentStart->copy()->addMinutes(
                $this->durationForAppointment($appointment, $fallbackDurationMinutes)
            );

            if ($slotStart->lt($appointmentEnd) && $slotEnd->gt($appointmentStart)) {
                return true;
            }
        }

        return false;
    }

    private function normalizeAppointmentType(?string $appointmentType): string
    {
        $appointmentType = strtolower((string) ($appointmentType ?: 'treatment'));

        return in_array($appointmentType, ['consultation', 'session', 'treatment'], true)
            ? $appointmentType
            : 'treatment';
    }

    private function resolveDurationMinutes(array $schedule, string $appointmentType): int
    {
        $duration = data_get($schedule, "duration_minutes.{$appointmentType}")
            ?? data_get($schedule, "durations_minutes.{$appointmentType}")
            ?? data_get(self::DEFAULT_SLOTS, "duration_minutes.{$appointmentType}");

        return max(5, (int) $duration);
    }

    private function durationForAppointment(Appointment $appointment, int $fallbackDurationMinutes): int
    {
        if (!empty($appointment->duration_minutes)) {
            return max(5, (int) $appointment->duration_minutes);
        }

        $appointmentType = $this->normalizeAppointmentType($appointment->appointment_type ?? null);

        return $appointmentType === 'session' ? 60 : $fallbackDurationMinutes;
    }
}
