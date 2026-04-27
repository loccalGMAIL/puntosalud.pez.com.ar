@extends('layouts.app')

@section('title', 'Seleccionar Pacientes a Atender - ' . config('app.name'))

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Breadcrumbs -->
    <nav class="flex mb-4" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                    <svg class="w-3 h-3 mr-2.5" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20">
                        <path d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2a1 1 0 0 0 1.414 1.414L2 10.414V18a2 2 0 0 0 2 2h3a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h3a2 2 0 0 0 2-2v-7.586l.293.293a1 1 0 0 0 1.414-1.414Z"/>
                    </svg>
                    Dashboard
                </a>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <svg class="w-3 h-3 text-gray-400 mx-1" aria-hidden="true" fill="none" viewBox="0 0 6 10">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                    </svg>
                    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2 dark:text-gray-400">Pacientes a Atender</span>
                </div>
            </li>
        </ol>
    </nav>

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                📋 Listado de Pacientes a Atender
            </h1>
            <p class="text-gray-600 dark:text-gray-400">
                Ver listado de pacientes programados por profesional y fecha
            </p>
        </div>
        <a href="{{ route('dashboard') }}"
           class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
            </svg>
            Volver al Dashboard
        </a>
    </div>

    <!-- Selector de Fecha -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-center">
            <div class="w-full max-w-sm">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 text-center">
                    📅 Fecha del Listado
                </label>
                <input type="date"
                       name="date"
                       id="dateSelector"
                       value="{{ $selectedDate->format('Y-m-d') }}"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white text-center">
            </div>
        </div>
    </div>

    <!-- Profesionales con Pacientes -->
    @if($professionalsWithAppointments->count() > 0)
        <div class="mt-8">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                👨‍⚕️ Profesionales con Pacientes Programados
                <span class="text-sm font-normal text-gray-500 dark:text-gray-400">
                    ({{ $professionalsWithAppointments->count() }} {{ $professionalsWithAppointments->count() === 1 ? 'profesional' : 'profesionales' }} con pacientes hoy)
                </span>
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($professionalsWithAppointments as $professional)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h4 class="font-medium text-gray-900 dark:text-white">
                                    Dr. {{ $professional['first_name'] }} {{ $professional['last_name'] }}
                                </h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ $professional['specialty']->name }}
                                </p>
                                <div class="mt-3 space-y-1">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">Pacientes:</span>
                                        <span class="font-medium text-gray-900 dark:text-white">{{ $professional['appointments_count'] }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">Horario:</span>
                                        <span class="font-medium text-gray-900 dark:text-white">
                                            {{ \Carbon\Carbon::parse($professional['first_appointment_time'])->format('H:i') }} -
                                            {{ \Carbon\Carbon::parse($professional['last_appointment_time'])->format('H:i') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                             <div class="flex flex-col gap-1 ml-3">
                                 <a href="{{ route('agenda.daily-schedule', ['professional_id' => $professional['id'], 'date' => $selectedDate->format('Y-m-d')]) }}"
                                    class="inline-flex items-center px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-800 text-xs font-medium rounded transition-colors">
                                    <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                    Ver
                                </a>
                                 <a href="{{ route('agenda.daily-schedule', ['professional_id' => $professional['id'], 'date' => $selectedDate->format('Y-m-d'), 'print' => '1']) }}" target="_blank"
                                    class="inline-flex items-center px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-800 text-xs font-medium rounded transition-colors">
                                    <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                                    </svg>
                                     Imprimir
                                 </a>

                                 <button type="button"
                                         onclick="shareViaWhatsAppFromButton(event)"
                                         data-professional-id="{{ $professional['id'] }}"
                                         data-date="{{ $selectedDate->format('Y-m-d') }}"
                                         class="inline-flex items-center px-3 py-1 bg-emerald-100 hover:bg-emerald-200 disabled:opacity-50 text-emerald-800 text-xs font-medium rounded transition-colors">
                                     <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 24 24">
                                         <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>
                                     </svg>
                                     Compartir
                                 </button>
                             </div>
                         </div>
                     </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="mt-8">
            <div class="text-center py-12 bg-gray-50 dark:bg-gray-800 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600">
                <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5a2.25 2.25 0 0 0 2.25-2.25m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5m-9-13.5h.008v.008H12V8.25z" />
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">No hay pacientes programados</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    No se encontraron profesionales con turnos programados para el {{ $selectedDate->format('d/m/Y') }}.
                    <br>
                    Puedes seleccionar otro día para ver los listados disponibles.
                </p>
            </div>
        </div>
    @endif
</div>

<script>
function shareViaWhatsAppFromButton(ev) {
    const btn = ev?.currentTarget;
    if (!btn) return;
    const professionalId = parseInt(btn.dataset.professionalId || '', 10);
    const date = btn.dataset.date;
    if (!professionalId || !date) {
        window.showToast('Error al enviar el listado.', 'error');
        return;
    }
    shareViaWhatsApp(professionalId, date, ev);
}

async function shareViaWhatsApp(professionalId, date, ev) {
    const btn = ev?.currentTarget;
    if (!btn) return;

    btn.disabled = true;
    const original = btn.innerHTML;
    btn.innerHTML = 'Enviando...';

    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 90000);

    try {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

        const r = await fetch('/agenda/daily-schedule/share-whatsapp', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf || '',
                'X-Requested-With': 'XMLHttpRequest'
            },
            signal: controller.signal,
            body: JSON.stringify({ professional_id: professionalId, date })
        });

        let data = null;
        try {
            data = await r.json();
        } catch (e) {
            data = null;
        }

        const success = !!(data && data.success);

        if (data && data.message) {
            window.showToast(data.message, success ? 'success' : 'error');
            return;
        }

        if (data && data.errors) {
            const firstError = Object.values(data.errors)?.flat()?.[0];
            window.showToast(firstError || 'Error al enviar el listado.', 'error');
            return;
        }

        window.showToast(success ? 'Listado enviado por WhatsApp al profesional.' : 'Error al enviar el listado.', success ? 'success' : 'error');
    } catch (e) {
        const msg = (e && e.name === 'AbortError')
            ? 'La operación demoró demasiado. Intentá nuevamente.'
            : 'Error al enviar el listado.';
        window.showToast(msg, 'error');
    } finally {
        clearTimeout(timeoutId);
        btn.disabled = false;
        btn.innerHTML = original;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.querySelector('#dateSelector');

    if (dateInput) {
        dateInput.addEventListener('change', function() {
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('date', this.value);
            window.location.href = currentUrl.toString();
        });
    }
});
</script>
@endsection
