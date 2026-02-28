@push('styles')
<meta name="csrf-token" content="{{ csrf_token() }}">
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
[x-cloak] { display: none !important; }

/* Estilos personalizados para Select2 */
.select2-container--default .select2-selection--single {
    background-color: transparent;
    border: 1px solid rgb(209 213 219);
    border-radius: 0.5rem;
    height: 42px;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 42px;
    padding-left: 12px;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 42px;
}
.select2-dropdown {
    border: 1px solid rgb(209 213 219);
    border-radius: 0.5rem;
}
.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: rgb(37 99 235);
}
.select2-search--dropdown .select2-search__field {
    border: 1px solid rgb(209 213 219);
    border-radius: 0.5rem;
    padding: 0.5rem;
}
/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .select2-container--default .select2-selection--single {
        background-color: rgb(55 65 81);
        border-color: rgb(75 85 99);
        color: white;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: white;
    }
    .select2-dropdown {
        background-color: rgb(55 65 81);
        border-color: rgb(75 85 99);
    }
    .select2-container--default .select2-results__option {
        background-color: rgb(55 65 81);
        color: white;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: rgb(29 78 216);
    }
    .select2-search--dropdown .select2-search__field {
        background-color: rgb(55 65 81);
        border-color: rgb(75 85 99);
        color: white;
    }
}
</style>
@endpush
