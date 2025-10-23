<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title">Historial de Transacciones</h5>
        </div>
        <div class="card-body">
            <?php if (empty($transactions)): ?>
                <p class="text-muted">No tienes transacciones registradas.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fecha Inicio</th>
                                <th>Fecha Fin</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $transaction): ?>
                                <tr>
                                    <td>#<?= $transaction['id_transaccion'] ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($transaction['start_datetime'])) ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($transaction['end_datetime'])) ?></td>
                                    <td>Iniciada el <?= $transaction['created_at'] ?></td>
                                    <td>$<?= number_format($transaction['items_count'], 2) ?></td>
                                    <td>
                                        <span class="badge bg-<?= 
                                            $transaction['state_name'] == 'completada' ? 'success' : 
                                            ($transaction['total_amount'] == 'pendiente' ? 'warning' : 'danger') 
                                        ?>">
                                            <?= ucfirst($transaction['total_amount']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="?page=transaction&id=<?= $transaction['id_transaction'] ?>" class="btn btn-sm btn-outline-primary">Detalles</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>