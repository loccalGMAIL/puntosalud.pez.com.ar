<?php

namespace Tests\Feature\Appointments;

use App\Models\Appointment;
use App\Models\Office;
use App\Models\Patient;
use App\Models\Professional;
use App\Models\Profile;
use App\Models\ProfileModule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentOfficePersistenceTest extends TestCase
{
    use RefreshDatabase;

    private function actingUserWithAppointmentsAccess(): void
    {
        $profile = Profile::create(['name' => 'Test Profile']);
        ProfileModule::create(['profile_id' => $profile->id, 'module' => 'appointments']);

        $this->actingAs(User::factory()->create(['profile_id' => $profile->id, 'is_active' => true]));
    }

    private function payloadFor(Appointment $appointment, array $overrides = []): array
    {
        return array_merge([
            'professional_id' => $appointment->professional_id,
            'patient_id' => $appointment->patient_id,
            'appointment_date' => $appointment->appointment_date->format('Y-m-d'),
            'appointment_time' => $appointment->appointment_date->format('H:i'),
            'duration' => $appointment->duration,
            'status' => $appointment->status,
        ], $overrides);
    }

    public function test_el_consultorio_se_conserva_cuando_el_request_no_envia_office_id(): void
    {
        $this->actingUserWithAppointmentsAccess();

        $office = Office::factory()->create();
        $appointment = Appointment::factory()->create(['office_id' => $office->id]);

        $this->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->putJson(route('appointments.update', $appointment), $this->payloadFor($appointment))
            ->assertOk();

        $this->assertSame($office->id, $appointment->fresh()->office_id);
    }

    public function test_el_consultorio_se_puede_borrar_eligiendo_sin_consultorio(): void
    {
        $this->actingUserWithAppointmentsAccess();

        $office = Office::factory()->create();
        $appointment = Appointment::factory()->create(['office_id' => $office->id]);

        $this->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->putJson(route('appointments.update', $appointment), $this->payloadFor($appointment, ['office_id' => '']))
            ->assertOk();

        $this->assertNull($appointment->fresh()->office_id);
    }

    public function test_el_consultorio_se_actualiza_cuando_el_request_manda_otro(): void
    {
        $this->actingUserWithAppointmentsAccess();

        $appointment = Appointment::factory()->create(['office_id' => Office::factory()->create()->id]);
        $otro = Office::factory()->create();

        $this->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->putJson(route('appointments.update', $appointment), $this->payloadFor($appointment, ['office_id' => $otro->id]))
            ->assertOk();

        $this->assertSame($otro->id, $appointment->fresh()->office_id);
    }

    public function test_la_agenda_expone_el_consultorio_del_turno(): void
    {
        $profile = Profile::create(['name' => 'Test Profile Agenda']);
        ProfileModule::create(['profile_id' => $profile->id, 'module' => 'agenda']);
        $this->actingAs(User::factory()->create(['profile_id' => $profile->id, 'is_active' => true]));

        $professional = Professional::factory()->create();
        $office = Office::factory()->create();
        $appointment = Appointment::factory()->create([
            'professional_id' => $professional->id,
            'patient_id' => Patient::factory()->create()->id,
            'office_id' => $office->id,
            'appointment_date' => now()->addDay()->setTime(10, 0),
            'status' => 'scheduled',
        ]);

        $response = $this->get(route('agenda.index', [
            'professional_id' => $professional->id,
            'month' => $appointment->appointment_date->format('Y-m'),
        ]))->assertOk();

        // El modal de la agenda lee appointment.office para preseleccionar el consultorio,
        // asi que la relacion tiene que venir serializada en el JSON inyectado en Alpine.
        $appointments = $response->viewData('appointments');
        $serialized = $appointments->flatten()->firstWhere('id', $appointment->id)->toArray();

        $this->assertArrayHasKey('office', $serialized);
        $this->assertSame($office->id, $serialized['office']['id']);
    }
}
