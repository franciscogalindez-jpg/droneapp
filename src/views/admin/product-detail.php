<?php include 'includes/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-6">
            <img src="assets/images/products/<?= htmlspecialchars($product['imagen_dron'] ?? 'default-drone.png') ?>" 
                 class="img-fluid rounded" 
                 alt="<?= htmlspecialchars($product['nombre_dron']) ?>"
                 onerror="this.src='assets/images/products/default-drone.png'">
        </div>
        <div class="col-md-6">
            <h1><?= htmlspecialchars($product['nombre_dron']) ?></h1>
            <p class="text-muted">Proveedor: <?= htmlspecialchars($product['nombre_proveedor']) ?></p>
            <p class="text-muted">Categoría: <?= htmlspecialchars($product['nombre_categoria']) ?></p>
            
            <div class="mb-4">
                <h4 class="text-success">$<?= number_format($product['precio_hora'], 2) ?> por hora</h4>
                <h5 class="text-success">$<?= number_format($product['precio_alquiler_dia'], 2) ?> por día</h5>
            </div>
            
            <h3>Especificaciones Técnicas</h3>
            <ul class="list-group list-group-flush mb-4">
                <li class="list-group-item">
                    <strong>Velocidad:</strong> <?= htmlspecialchars($product['velocidad_categoria']) ?>
                </li>
                <li class="list-group-item">
                    <strong>Batería:</strong> <?= htmlspecialchars($product['bateria_categoria']) ?>
                </li>
                <li class="list-group-item">
                    <strong>Estabilidad:</strong> <?= htmlspecialchars($product['estabilidad_categoria']) ?>
                </li>
                <li class="list-group-item">
                    <strong>Capacidad de Carga:</strong> <?= htmlspecialchars($product['capacidad_carga_categoria']) ?>
                </li>
            </ul>
            
            <h3>Descripción</h3>
            <p><?= htmlspecialchars($product['descripcion_dron']) ?></p>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <form action="?page=cart" method="POST" class="mt-4">
                    <input type="hidden" name="product_id" value="<?= $product['id_dron'] ?>">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="quantity" class="form-label">Cantidad</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" min="1" value="1">
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Tipo</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="is_rental" id="rental" value="1" checked>
                                <label class="form-check-label" for="rental">
                                    Alquiler por hora
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="is_rental" id="purchase" value="0">
                                <label class="form-check-label" for="purchase">
                                    Compra
                                </label>
                            </div>
                        </div>
                    </div>
                    <button type="submit" name="add_to_cart" class="btn btn-primary btn-lg">
                        Añadir al Carrito
                    </button>
                </form>
            <?php else: ?>
                <div class="alert alert-info mt-4">
                    <a href="?page=login" class="alert-link">Inicia sesión</a> para añadir productos al carrito.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<!-- Modifica el formulario para que tenga en cuenta la opción de compra -->
<form method="post" action="?page=cart" class="mt-4">
    <input type="hidden" name="product_id" value="<?= $product['id_dron'] ?>">
    
    <div class="row g-3">
        <div class="col-md-6">
            <label for="quantity" class="form-label">Cantidad</label>
            <input type="number" class="form-control" id="quantity" name="quantity" value="1" min="1">
        </div>
        
        <div class="col-md-6">
            <label class="form-label">Tipo</label>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="is_rental" id="rentalOption" value="1" checked>
                <label class="form-check-label" for="rentalOption">Alquiler</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="is_rental" id="purchaseOption" value="0">
                <label class="form-check-label" for="purchaseOption">Compra</label>
            </div>
        </div>
        
        <div class="col-md-6 rental-dates">
            <label for="start_date" class="form-label">Fecha de inicio</label>
            <input type="datetime-local" class="form-control" id="start_date" name="start_date">
        </div>
        
        <div class="col-md-6 rental-dates">
            <label for="end_date" class="form-label">Fecha de fin</label>
            <input type="datetime-local" class="form-control" id="end_date" name="end_date">
        </div>
        
        <div class="col-12">
            <button type="submit" name="add_to_cart" class="btn btn-primary btn-lg w-100">
                <i class="bi bi-cart-plus me-2"></i> Añadir al carrito
            </button>
        </div>
    </div>
</form>

<script>
// Actualiza el script para manejar mejor la opción de compra
document.addEventListener('DOMContentLoaded', function() {
    const rentalOption = document.getElementById('rentalOption');
    const purchaseOption = document.getElementById('purchaseOption');
    const rentalDates = document.querySelectorAll('.rental-dates');
    
    function toggleDateFields() {
        const isRental = rentalOption.checked;
        rentalDates.forEach(el => {
            el.style.display = isRental ? 'block' : 'none';
            const inputs = el.querySelectorAll('input');
            inputs.forEach(input => {
                input.required = isRental;
            });
        });
    }
    
    // Inicializar estado
    toggleDateFields();
    
    // Escuchar cambios
    rentalOption.addEventListener('change', toggleDateFields);
    purchaseOption.addEventListener('change', toggleDateFields);
    
    // Configurar fechas mínimas
    const now = new Date();
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    
    const formatDateForInput = (date) => {
        const offset = date.getTimezoneOffset();
        const adjustedDate = new Date(date.getTime() - (offset * 60 * 1000));
        return adjustedDate.toISOString().slice(0, 16);
    };
    
    startDateInput.min = formatDateForInput(now);
    endDateInput.min = formatDateForInput(new Date(now.getTime() + 3600000));
    
    startDateInput.addEventListener('change', function() {
        if (this.value) {
            endDateInput.min = this.value;
            if (!endDateInput.value || new Date(endDateInput.value) < new Date(this.value)) {
                const endDate = new Date(new Date(this.value).getTime() + 3600000);
                endDateInput.value = formatDateForInput(endDate);
            }
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>