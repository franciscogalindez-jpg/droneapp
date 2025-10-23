<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title">Mi Perfil</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 100px; height: 100px;">
                            <i class="fas fa-user fa-3x"></i>
                        </div>
                    </div>
                    <h5 class="card-title"><?= htmlspecialchars($user['username']) ?></h5>
                    <p class="card-text"><strong>Rol:</strong> <?= htmlspecialchars($user['role_name']) ?></p>
                    <p class="card-text"><strong>Género:</strong> <?= htmlspecialchars($user['gender_name']) ?></p>
                    <a href="?page=profile&action=edit" class="btn btn-primary btn-sm">Editar Perfil</a>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title">Información de Contacto</h5>
                </div>
                <div class="card-body">
                    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                    <p><strong>Teléfono:</strong> <?= htmlspecialchars($user['phone']) ?></p>
                    <p><strong>Dirección:</strong> <?= htmlspecialchars($user['address'] ?? 'No especificada') ?></p>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Últimas Transacciones</h5>
                    <a href="?page=profile&action=history" class="btn btn-sm btn-light">Ver todo</a>
                </div>
                <div class="card-body">
                    <?php if (empty($transactions)): ?>
                        <p class="text-muted">No hay transacciones recientes.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Fecha</th>
                                        <th>Items</th>
                                        <th>Total</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transactions as $transaction): ?>
                                        <tr>
                                            <td>#<?= $transaction['id_transaccion'] ?></td>
                                            <td><?= date('d/m/Y', strtotime($transaction['fecha_inicio'])) ?></td>
                                            <td><?= $transaction['items_count'] ?></td>
                                            <td>$<?= number_format($transaction['total'], 2) ?></td>
                                            <td>
                                                <a href="?page=transaction&id=<?= $transaction['id_transaccion'] ?>" class="btn btn-sm btn-outline-primary">Detalles</a>
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
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>