<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AppointmentSetting;
use App\Models\ProfessionalSlotBlock;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SlotBlockController extends Controller
{
    /**
     * Bloquea un rango horario dividiéndolo en un registro por turno.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'professional_id' => 'required|exists:professionals,id',
            'date' => 'required|date_format:Y-m-d|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'reason' => 'nullable|string|max:255',
        ]);

        $slotMinutes = (int) (AppointmentSetting::where('professional_id', $validated['professional_id'])
            ->value('default_duration_minutes') ?: 30);

        $dayStart = Carbon::createFromFormat('Y-m-d H:i', $validated['date'].' '.$validated['start_time']);
        $rangeEnd = Carbon::createFromFormat('Y-m-d H:i', $validated['date'].' '.$validated['end_time']);

        $appointments = Appointment::where('professional_id', $validated['professional_id'])
            ->whereDate('appointment_date', $validated['date'])
            ->where('status', 'scheduled')
            ->get();

        $created = 0;
        $skipped = 0;

        $userId = $request->user()?->id;

        DB::transaction(function () use ($validated, $slotMinutes, $dayStart, $rangeEnd, $appointments, $userId, &$created, &$skipped) {
            $cursor = $dayStart->copy();

            while ($cursor->lt($rangeEnd)) {
                $slotStart = $cursor->copy();
                $slotEnd = $cursor->copy()->addMinutes($slotMinutes)->min($rangeEnd);
                $cursor->addMinutes($slotMinutes);

                $hasAppointment = $appointments->contains(function ($appointment) use ($slotStart, $slotEnd) {
                    $start = Carbon::parse($appointment->appointment_date);
                    $end = $start->copy()->addMinutes(max((int) $appointment->duration, 1));

                    return $slotStart->lt($end) && $slotEnd->gt($start);
                });

                $alreadyBlocked = ProfessionalSlotBlock::forProfessional($validated['professional_id'])
                    ->forDate($validated['date'])
                    ->overlapping($slotStart->format('H:i:s'), $slotEnd->format('H:i:s'))
                    ->exists();

                if ($hasAppointment || $alreadyBlocked) {
                    $skipped++;

                    continue;
                }

                ProfessionalSlotBlock::create([
                    'professional_id' => $validated['professional_id'],
                    'block_date' => $validated['date'],
                    'start_time' => $slotStart->format('H:i:s'),
                    'end_time' => $slotEnd->format('H:i:s'),
                    'reason' => $validated['reason'] ?? null,
                    'created_by' => $userId,
                ]);
                $created++;
            }
        });

        return response()->json(['success' => true, 'created' => $created, 'skipped' => $skipped]);
    }

    /**
     * Desbloquea un turno.
     */
    public function destroy(ProfessionalSlotBlock $slotBlock): JsonResponse
    {
        $slotBlock->delete();

        return response()->json(['success' => true]);
    }
}
