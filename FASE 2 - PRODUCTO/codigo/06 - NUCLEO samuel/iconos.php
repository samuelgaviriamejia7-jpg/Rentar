<?php

function icono($nombre, $relleno = false) {
    $trazos = [
        'carro'    => '<path d="M5 17H3v-4l2-5h14l2 5v4h-2"/><path d="M5 13h14"/><circle cx="7.5" cy="17" r="2"/><circle cx="16.5" cy="17" r="2"/><path d="M9.5 17h5"/>',
        'moto'     => '<circle cx="5" cy="17" r="3"/><circle cx="19" cy="17" r="3"/><path d="M8 17h5l3-6h3"/><path d="M13 11l-3-3H7"/><path d="M16 8h3"/>',
        'corazon'  => '<path d="M12 20s-7-4.3-7-9a4 4 0 0 1 7-2.6A4 4 0 0 1 19 11c0 4.7-7 9-7 9z"/>',
        'estrella' => '<path d="M12 4l2.4 5 5.6.8-4 3.9 1 5.5-5-2.7-5 2.7 1-5.5-4-3.9 5.6-.8z"/>',
        'campana'  => '<path d="M18 15V10a6 6 0 0 0-12 0v5l-1.5 2.5h15z"/><path d="M10 20h4"/>',
        'sol'      => '<circle cx="12" cy="12" r="4"/><path d="M12 3v2"/><path d="M12 19v2"/><path d="M3 12h2"/><path d="M19 12h2"/><path d="M5.6 5.6l1.4 1.4"/><path d="M17 17l1.4 1.4"/><path d="M18.4 5.6L17 7"/><path d="M7 17l-1.4 1.4"/>',
        'luna'     => '<path d="M20 14.5A8 8 0 0 1 9.5 4a8 8 0 1 0 10.5 10.5z"/>',
        'check'    => '<path d="M5 12.5l4.5 4.5L19 7.5"/>',
        'salir'    => '<path d="M10 5H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h4"/><path d="M15 8l4 4-4 4"/><path d="M19 12H10"/>',
    ];

    if (!isset($trazos[$nombre])) return '';

    return '<svg class="ico" viewBox="0 0 24 24" fill="' . ($relleno ? 'currentColor' : 'none')
         . '" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">'
         . $trazos[$nombre] . '</svg>';
}
