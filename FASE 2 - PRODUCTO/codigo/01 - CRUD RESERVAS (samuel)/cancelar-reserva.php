<?php
require_once __DIR__ . '/../06 - NUCLEO samuel/funciones.php';
requiereLogin();

$id = (int)($_GET['id'] ?? 0);

$st = $pdo->prepare("SELECT estado FROM reservas WHERE id_reserva = ? AND id_usuario = ?");
$st->execute([$id, usuarioId()]);
$estado = $st->fetchColumn();

if ($estado === false) {
    mensaje('Reserva no encontrada.', 'error');
    ir('mis-reservas.php');
}

if ($estado === 'confirmada') {
    try {
        $pdo->beginTransaction();
        $pdo->prepare("UPDATE reservas SET estado = 'cancelada' WHERE id_reserva = ? AND id_usuario = ?")
            ->execute([$id, usuarioId()]);
        registrarCambio($pdo, $id, 'estado', $estado, 'cancelada', usuarioId());
        $pdo->commit();
        mensaje('Reserva cancelada.');
    } catch (PDOException $ex) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        mensaje('No se pudo cancelar la reserva.', 'error');
    }
} else {
    mensaje('Esa reserva ya no se puede cancelar.', 'error');
}

ir('mis-reservas.php');
