<?php

namespace App\Http\Controllers;

use App\Models\Professional;
use App\Models\ProfessionalAbsence;
use App\Models\ProfessionalSchedule;
use App\Models\ScheduleException;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ProfessionalAbsenceController extends Controller
{
    /**
     * Devuelve los datos del calendario mensual en JSON para el modal.
     */
    public function monthData(Request $request, Professional $professional)
    {
        $year  = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);

        $date            = Carbon::createFromDate($year, $month, 1);
        $startOfCalendar = $date->copy()->startOfWeek();
        $endOfCalendar   = $date->copy()->endOfMonth()->endOfWeek();

        $professionalSchedules = ProfessionalSchedule::where('professional_id', $professional->id)
            ->active()
            ->get()
            ->keyBy('day_of_week');

        $absences = ProfessionalAbsence::forProfessional($professional->id)
            ->betweenDates($startOfCalendar, $endOfCalendar)
            ->get()
            ->keyBy(fn ($a) => $a->absence_date->format('Y-m-d'));

        $holidays = ScheduleException::holidays()
            ->active()
            ->whereBetween('exception_date', [$startOfCalendar, $endOfCalendar])
            ->get()
            ->keyBy(fn ($h) => $h->exception_date->format('Y-m-d'));

        $days = [];
        $currentDay = $startOfCalendar->copy();

        while ($currentDay->lte($endOfCalendar)) {
            $dow = $currentDay->dayOfWeek === 0 ? 7 : $currentDay->dayOfWeek;

            if ($dow !== 7) {
                $dayKey = $currentDay->format('Y-m-d');
                $days[] = [
                    'date'           => $dayKey,
                    'day'            => $currentDay->day,
                    'isCurrentMonth' => $currentDay->month === $date->month,
                    'isToday'        => $currentDay->isToday(),
                    'hasSchedule'    => $professionalSchedules->has($dow),
                    'isHoliday'      => $holidays->has($dayKey),
                    'isAbsent'       => $absences->has($dayKey),
                ];
            }

            $currentDay->addDay();
        }

        $prevDate = $date->copy()->subMonth();
        $nextDate = $date->copy()->addMonth();

        return response()->json([
            'year'      => $year,
            'month'     => $month,
            'monthName' => ucfirst($date->locale('es')->isoFormat('MMMM YYYY')),
            'prevYear'  => $prevDate->year,
            'prevMonth' => $prevDate->month,
            'nextYear'  => $nextDate->year,
            'nextMonth' => $nextDate->month,
            'days'      => $days,
        ]);
    }

    public function toggle(Request $request, Professional $professional)
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
        ]);

        $date = $request->input('date');

        $existing = ProfessionalAbsence::where('professional_id', $professional->id)
            ->where('absence_date', $date)
            ->first();

        if ($existing) {
            $existing->delete();

            return response()->json(['success' => true, 'status' => 'removed']);
        }

        ProfessionalAbsence::create([
            'professional_id' => $professional->id,
            'absence_date'    => $date,
        ]);

        return response()->json(['success' => true, 'status' => 'added']);
    }
}
