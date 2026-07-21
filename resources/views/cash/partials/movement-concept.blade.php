{{--
    Celda "Concepto" de un movimiento de caja.
    Muestra el título limpio (sin tipo de movimiento ni medio de pago) y, como
    sub-líneas, el profesional y —para reembolsos— el recibo original y el #id
    del movimiento anulado.

    Parámetros:
      $movement : App\Models\CashMovement
      $print    : bool (opcional) estilos compactos para impresión
--}}
@php
    $print = $print ?? false;
    $professional = $movement->conceptProfessionalName();
    $refund = $movement->refundInfo();
    $subClass = $print
        ? 'text-[10px] text-gray-500 print:text-black'
        : 'text-xs text-gray-500 dark:text-gray-400';
@endphp
<div>
    <div class="{{ $print ? '' : 'font-medium' }}">{{ $movement->conceptTitle() }}</div>

    @if($professional)
        <div class="{{ $subClass }}">Prof: {{ $professional }}</div>
    @endif

    @if($refund)
        @if($refund['receipt'])
            <div class="{{ $subClass }}">Recibo #{{ $refund['receipt'] }}</div>
        @endif
        @if($refund['original_movement_id'])
            <div class="{{ $subClass }}">Anula Mov. #{{ $refund['original_movement_id'] }}</div>
        @endif
    @endif
</div>
