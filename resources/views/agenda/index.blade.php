@extends('layouts.app')

@section('title', 'Agenda - ' . config('app.name'))
@section('mobileTitle', 'Agenda')

@section('content')
<div class="p-6" x-data="appointmentModal()" x-init="init()">

    {{-- Header: breadcrumb, título, selector de profesional, nav de mes --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-gray-700 dark:hover:text-gray-200">Dashboard</a>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
                <span>Agenda</span>
            </nav>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Agenda</h1>
        </div>

        <div class="flex flex-col sm:flex-row gap-3">
            <!-- Professional Selector -->
            <form method="GET" action="{{ route('agenda.index') }}" class="flex gap-2 flex-1" id="professional-form">
                <input type="hidden" name="month" value="{{ $currentMonth }}">
                <select name="professional_id"
                        id="agenda-professional-select"
                        style="width: 500px;"
                        class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Seleccionar profesional</option>
                    @foreach($professionals as $professional)
                        <option value="{{ $professional->id }}"
                                data-specialty="{{ $professional->specialty->name }}"
                                {{ $selectedProfessional == $professional->id ? 'selected' : '' }}>
                            Dr. {{ $professional->full_name }} - {{ $professional->specialty->name }}
                        </option>
                    @endforeach
                </select>
            </form>

            <!-- Month Navigation -->
            <div class="flex items-center gap-2">
                <a href="{{ route('agenda.index', ['month' => $date->copy()->subMonth()->format('Y-m'), 'professional_id' => $selectedProfessional]) }}"
                   class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                    </svg>
                </a>

                <h2 class="text-lg font-semibold text-gray-900 dark:text-white min-w-[180px] text-center">
                    {{ $date->locale('es')->isoFormat('MMMM YYYY') }}
                </h2>

                <a href="{{ route('agenda.index', ['month' => $date->copy()->addMonth()->format('Y-m'), 'professional_id' => $selectedProfessional]) }}"
                   class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </a>
            </div>
        </div>
    </div>

    @include('agenda.partials.cash-alerts')

    @include('agenda.partials.calendar')

    {{-- Modal compartido con vista Turnos --}}
    @include('appointments.modal')

    {{-- Modal de paciente --}}
    <div x-data="patientModal()">
        @include('patients.modal')
    </div>

    @include('agenda.partials.day-modal')

</div>
@endsection

@include('agenda.partials.styles')
@include('agenda.partials.scripts')
