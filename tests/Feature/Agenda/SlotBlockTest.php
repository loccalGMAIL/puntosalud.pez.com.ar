<?php

namespace Tests\Feature\Agenda;

use App\Models\Appointment;
use App\Models\AppointmentSetting;
use App\Models\Patient;
use App\Models\Professional;
use App\Models\ProfessionalSchedule;
use App\Models\ProfessionalSlotBlock;
use App\Models\Profile;
use App\Models\ProfileModule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlotBlockTest extends TestCase
{
    use RefreshDatabase;

    private function actingUserWithModules(array $modules): User
    {
        $profile = Profile::create(['name' => 'Perfil '.implode('-', $modules)]);
        foreach ($modules as $module) {
            ProfileModule::create(['profile_id' => $profile->id, 'module' => $module]);
        }

        $user = User::factory()->create(['profile_id' => $profile->id, 'is_active' => true]);
        $this->actingAs($user);

        return $user;
    }

    private function professionalWorkingTomorrow(): Professional
    {
        $professional = Professional::factory()->create();
        ProfessionalSchedule::create([
            'professional_id' => $professional->id,
            'day_of_week' => now()->addDay()->dayOfWeekIso,
            'start_time' => '08:00',
            'end_time' => '18:00',
            'is_active' => true,
        ]);

        return $professional;
    }

    private function blockPayload(Professional $professional, array $overrides = []): array
    {
        return array_merge([
            'professional_id' => $professional->id,
            'date' => now()->addDay()->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:30',
            'reason' => 'Pedido del profesional',
        ], $overrides);
    }

    public function test_bloquear_un_rango_crea_un_registro_por_turno_y_guarda_quien_lo_hizo(): void
    {
        $user = $this->actingUserWithModules(['agenda']);
        $professional = Professional::factory()->create();
        AppointmentSetting::create([
            'professional_id' => $professional->id,
            'default_duration_minutes' => 30,
        ]);

        $this->postJson(route('agenda.slot-blocks.store'), $this->blockPayload($professional))
            ->assertOk()
            ->assertJson(['success' => true, 'created' => 3, 'skipped' => 0]);

        $blocks = ProfessionalSlotBlock::orderBy('start_time')->get();
        $this->assertSame(['10:00', '10:30', '11:00'], $blocks->map(fn ($b) => substr($b->start_time, 0, 5))->all());
        $this->assertSame('11:30', substr($blocks->last()->end_time, 0, 5));
        $this->assertSame($user->id, $blocks->first()->created_by);
        $this->assertSame('Pedido del profesional', $blocks->first()->reason);
    }

    public function test_bloquear_un_rango_omite_los_turnos_ya_ocupados_y_los_ya_bloqueados(): void
    {
        $this->actingUserWithModules(['agenda']);
        $professional = Professional::factory()->create();
        AppointmentSetting::create([
            'professional_id' => $professional->id,
            'default_duration_minutes' => 30,
        ]);

        Appointment::factory()->create([
            'professional_id' => $professional->id,
            'patient_id' => Patient::factory()->create()->id,
            'appointment_date' => now()->addDay()->setTime(10, 30),
            'duration' => 30,
            'status' => 'scheduled',
        ]);
        ProfessionalSlotBlock::factory()->create([
            'professional_id' => $professional->id,
            'block_date' => now()->addDay()->toDateString(),
            'start_time' => '11:00:00',
            'end_time' => '11:30:00',
        ]);

        $this->postJson(route('agenda.slot-blocks.store'), $this->blockPayload($professional))
            ->assertOk()
            ->assertJson(['created' => 1, 'skipped' => 2]);

        $this->assertSame(2, ProfessionalSlotBlock::count());
    }

    public function test_desbloquear_elimina_el_bloqueo(): void
    {
        $this->actingUserWithModules(['agenda']);
        $block = ProfessionalSlotBlock::factory()->create();

        $this->deleteJson(route('agenda.slot-blocks.destroy', $block))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertModelMissing($block);
    }

    public function test_no_se_puede_bloquear_en_el_pasado_ni_con_rango_invertido(): void
    {
        $this->actingUserWithModules(['agenda']);
        $professional = Professional::factory()->create();

        $this->postJson(route('agenda.slot-blocks.store'), $this->blockPayload($professional, [
            'date' => now()->subDay()->toDateString(),
        ]))->assertJsonValidationErrors('date');

        $this->postJson(route('agenda.slot-blocks.store'), $this->blockPayload($professional, [
            'start_time' => '11:00',
            'end_time' => '10:00',
        ]))->assertJsonValidationErrors('end_time');

        $this->assertSame(0, ProfessionalSlotBlock::count());
    }

    public function test_sin_el_modulo_agenda_no_se_puede_bloquear_ni_desbloquear(): void
    {
        $this->actingUserWithModules(['patients']);
        $professional = Professional::factory()->create();
        $block = ProfessionalSlotBlock::factory()->create();

        $this->postJson(route('agenda.slot-blocks.store'), $this->blockPayload($professional))->assertForbidden();
        $this->deleteJson(route('agenda.slot-blocks.destroy', $block))->assertForbidden();

        $this->assertModelExists($block);
    }

    public function test_no_se_puede_crear_un_turno_en_un_horario_bloqueado(): void
    {
        $this->actingUserWithModules(['appointments']);
        $professional = $this->professionalWorkingTomorrow();
        $patient = Patient::factory()->create();
        ProfessionalSlotBlock::factory()->create([
            'professional_id' => $professional->id,
            'block_date' => now()->addDay()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '10:30:00',
        ]);

        $this->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->postJson(route('appointments.store'), [
                'professional_id' => $professional->id,
                'patient_id' => $patient->id,
                'appointment_date' => now()->addDay()->toDateString(),
                'appointment_time' => '10:00',
                'duration' => 30,
                'status' => 'scheduled',
            ])
            ->assertStatus(422)
            ->assertJsonPath('errors.appointment_time.0', 'El horario está bloqueado. Desbloquealo desde la agenda para asignar el turno.');

        $this->assertSame(0, Appointment::count());
    }

    public function test_la_agenda_expone_los_bloqueos_del_profesional_seleccionado(): void
    {
        $this->actingUserWithModules(['agenda']);
        $professional = Professional::factory()->create();
        $block = ProfessionalSlotBlock::factory()->create([
            'professional_id' => $professional->id,
            'block_date' => now()->addDay()->toDateString(),
            'reason' => 'Reunión',
        ]);

        $response = $this->get(route('agenda.index', [
            'professional_id' => $professional->id,
            'month' => now()->addDay()->format('Y-m'),
            'date' => now()->addDay()->toDateString(),
        ]))->assertOk();

        $day = $response->viewData('slotBlocks')->get(now()->addDay()->toDateString());
        $this->assertSame($block->id, $day[0]['id']);
        $this->assertSame('10:00', $day[0]['start']);
        $this->assertSame('Reunión', $day[0]['reason']);
    }
}
