<?php include 'includes/header.php'; ?>

<div class="container py-5">
    <h1 class="mb-4">Tu Carrito de Reservas</h1>
    
    <?php if (empty($cartItems)): ?>
    <p>Tu carrito está vacío.</p>
    <a href="?page=catalog" class="btn btn-primary">Seguir alquilando</a>
<?php else: ?>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Producto</th>
                <th class="text-end">Precio Unitario</th>
                <th class="text-center">Cantidad</th>
                <th>Tiempo</th>
                <th class="text-end">Subtotal</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cartItems as $item): ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <img src="../assets/images/products/<?= htmlspecialchars($item['drone']['drone_image'] ?? 'default-drone.jpg') ?>"
                                 width="60" class="me-3 rounded"
                                 alt="<?= htmlspecialchars($item['drone']['name']) ?>">
                            <div>
                                <h6 class="mb-0"><?= htmlspecialchars($item['drone']['name']) ?></h6>
                                <small class="text-muted">
                                    <?php if ($item['start_date'] && $item['end_date']): ?>
                                        Alquiler: <?= date('d/m/Y H:i', strtotime($item['start_date'])) ?> a <?= date('d/m/Y H:i', strtotime($item['end_date'])) ?>
                                    <?php else: ?>
                                        Compra
                                    <?php endif; ?>
                                </small>
                            </div>
                        </div>
                    </td>
                    <td class="text-end">
                        $<?= number_format($item['daily_rate']) ?>
                        <?php if ($item['start_date'] && $item['end_date']): ?>
                            <small class="text-muted">/día</small>
                        <?php else: ?>
                            <br><small class="text-muted">por unidad</small>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <form method="post" action="?page=cart" class="d-inline-block">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                            <input type="hidden" name="drone_id" value="<?= $item['drone_id'] ?>">
                            <div class="input-group input-group-sm" style="width: 90px;">
                                <button type="button" class="btn btn-outline-secondary" onclick="this.parentNode.querySelector('input[type=number]').stepDown()">-</button>
                                <input type="number" name="quantity" value="<?= $item['quantity'] ?>"
                                    min="1" class="form-control form-control-sm text-center"
                                    max="<?= htmlspecialchars($item['drone']['drone_stock']) ?>">
                                <button type="button" class="btn btn-outline-secondary" onclick="this.parentNode.querySelector('input[type=number]').stepUp()">+</button>
                            </div>
                            <button type="submit" name="update_quantity" class="btn btn-sm btn-outline-primary mt-2">
                                <i class="bi bi-arrow-clockwise"></i> Actualizar
                            </button>
                        </form>
                    </td>
                    <td>
                        <?php if (isset($item['rental_days'])): ?>
                            <?= $item['rental_days'] ?> días
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </td>
                    <td class="text-end">
                        $<?= number_format($item['subtotal']) ?>
                    </td>
                    <td>
                        <a href="?page=cart&remove=<?= $item['drone_id'] ?>"
                           class="btn btn-sm btn-danger"
                           onclick="return confirm('¿Eliminar este producto del carrito?')">
                            <i class="bi bi-trash"></i> Eliminar
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <tr class="table-active">
                <td class="text-start fw-bold" colspan="4">Costo de envío: $<?= number_format($shippingCost, 2) ?></td>
                <td class="text-end fw-bold">Total:</td>
                <td class="text-end fw-bold">$<?= number_format($total, 1) ?></td>
            </tr>
        </tbody>
    </table>

    <div class="d-flex justify-content-between mt-3">
        <a href="?page=catalog" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i> Seguir comprando</a>
        <div>
            <form method="post" action="?page=cart&action=clear" class="d-inline">
                <input 
                    type="hidden" 
                    name="csrf_token" 
                    value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"
                >
                <button type="submit" class="btn btn-danger me-2" name="clear_cart">
                    <i class="bi bi-trash me-2"></i> Vaciar Carrito
                </button>
            </form>
            <a href="?page=checkout" class="btn btn-success"><i class="bi bi-check-circle me-2"></i> Proceder al Pago</a>
        </div>
    </div>
<?php endif; ?>
</div>

<style>
    .table th, .table td {
        vertical-align: middle;
    }
    .input-group-sm button {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        border-radius: 0.2rem;
    }
    .input-group-sm input[type=number] {
        height: auto;
    }
</style>

<?php include 'includes/footer.php'; ?>