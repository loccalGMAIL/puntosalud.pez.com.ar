<?php

namespace Tests\Unit\Services;

use App\Services\WhatsAppDispatchWindow;
use Carbon\Carbon;
use Tests\TestCase;

class WhatsAppDispatchWindowTest extends TestCase
{
    private WhatsAppDispatchWindow $window;

    protected function setUp(): void
    {
        parent::setUp();
        // Lun-Sab (1-6), 09:00-21:00
        $this->window = new WhatsAppDispatchWindow([1, 2, 3, 4, 5, 6], '09:00', '21:00');
    }

    public function test_ideal_time_within_window_returns_ideal_time(): void
    {
        $idealTime = Carbon::parse('2026-05-18 10:00:00'); // lunes
        $dispatch = $this->window->computeDispatchTime($idealTime);

        $this->assertEquals('2026-05-18 10:00:00', $dispatch->toDateTimeString());
    }

    public function test_ideal_time_before_window_open_dispatches_at_window_start_same_day(): void
    {
        // Turno mañana 08:30, hours_before=24 → idealTime = hoy 08:30
        // Debe despachar hoy al abrir ventana, NO ayer a las 20:40
        $idealTime = Carbon::parse('2026-05-18 08:30:00'); // lunes
        $dispatch = $this->window->computeDispatchTime($idealTime);

        $this->assertEquals('2026-05-18 09:00:00', $dispatch->toDateTimeString(),
            'Debe esperar a la apertura de ventana del mismo día, no retroceder al día anterior');
    }

    public function test_ideal_time_at_or_after_window_end_dispatches_at_safe_cutoff_same_day(): void
    {
        $idealTime = Carbon::parse('2026-05-18 21:30:00'); // lunes, después del cierre
        $dispatch = $this->window->computeDispatchTime($idealTime);

        $expected = '2026-05-18 20:40:00'; // 21:00 - 20 min
        $this->assertEquals($expected, $dispatch->toDateTimeString());
    }

    public function test_ideal_time_on_blocked_day_dispatches_on_previous_allowed_day(): void
    {
        // Domingo (0) no está en sendDays
        $idealTime = Carbon::parse('2026-05-17 14:00:00'); // domingo
        $dispatch = $this->window->computeDispatchTime($idealTime);

        // El último día permitido anterior es sábado 16
        $this->assertEquals('2026-05-16 20:40:00', $dispatch->toDateTimeString());
    }

    public function test_is_allowed_at_returns_true_within_window(): void
    {
        $this->assertTrue($this->window->isAllowedAt(Carbon::parse('2026-05-18 09:00:00')));
        $this->assertTrue($this->window->isAllowedAt(Carbon::parse('2026-05-18 15:00:00')));
        $this->assertTrue($this->window->isAllowedAt(Carbon::parse('2026-05-18 20:59:00')));
    }

    public function test_is_allowed_at_returns_false_outside_window(): void
    {
        $this->assertFalse($this->window->isAllowedAt(Carbon::parse('2026-05-18 08:59:00')));
        $this->assertFalse($this->window->isAllowedAt(Carbon::parse('2026-05-18 21:00:00')));
        $this->assertFalse($this->window->isAllowedAt(Carbon::parse('2026-05-17 15:00:00'))); // domingo
    }
}
