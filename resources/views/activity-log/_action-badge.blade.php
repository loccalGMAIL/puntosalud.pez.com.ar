@php
$badges = [
    'created' => ['label' => 'Creó',           'classes' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300'],
    'updated' => ['label' => 'Modificó',        'classes' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300'],
    'deleted' => ['label' => 'Eliminó',         'classes' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300'],
    'login'   => ['label' => 'Inició sesión',   'classes' => 'bg-violet-100 text-violet-800 dark:bg-violet-900/40 dark:text-violet-300'],
    'logout'  => ['label' => 'Cerró sesión',    'classes' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'],
];
$badge = $badges[$action] ?? ['label' => $action, 'classes' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'];
@endphp
<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $badge['classes'] }}">
    {{ $badge['label'] }}
</span>
