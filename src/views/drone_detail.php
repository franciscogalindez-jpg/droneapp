<?php include 'includes/header.php'; ?>

<div class="container mt-4">
    <?php if ($drone): ?>
        <div class="row">
            <div class="col-md-6">
                <!-- Galería de imágenes -->
                <div class="product-gallery mb-4">
                    <div class="main-image mb-3">
                        <img src="../assets/images/products/<?= htmlspecialchars($drone['drone_image'] ?? 'default-drone.jpg') ?>" 
                             class="img-fluid rounded-3" 
                             alt="<?= htmlspecialchars($drone['name']) ?>"
                             id="mainProductImage">
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <h1 class="mb-3"><?= htmlspecialchars($drone['name']) ?></h1>
                
                <div class="d-flex align-items-center mb-3">
                    <div class="rating text-warning me-3">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-half"></i>
                    </div>
                    <span class="text-muted">4.5 de 5 (24 reseñas)</span>
                </div>
                
                <div class="bg-light p-3 rounded-3 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Precio por día:</span>
                        <h3 class="mb-0 text-primary">$<?= number_format($drone['daily_rate'], 2) ?></h3>
                    </div>
                </div>
                
                <div class="mb-4">
                    <h4 class="mb-3">Descripción</h4>
                    <p><?= nl2br(htmlspecialchars($drone['description'])) ?></p>
                </div>
                
                <div class="mb-4">
                    <h4 class="mb-3">Especificaciones</h4>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="text-muted">Categoría:</span>
                            <span><?= htmlspecialchars($drone['category_name']) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="text-muted">Velocidad:</span>
                            <span><?= htmlspecialchars($drone['category_speed']) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="text-muted">Batería:</span>
                            <span><?= htmlspecialchars($drone['category_battery']) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="text-muted">Capacidad de carga:</span>
                            <span><?= htmlspecialchars($drone['category_load']) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="text-muted">Estabilidad:</span>
                            <span><?= htmlspecialchars($drone['category_stability']) ?></span>
                        </li>                        
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="text-muted">Proveedor:</span>
                            <span><?= htmlspecialchars($drone['provider_name']) ?></span>
                        </li>
                    </ul>
                </div>
                <?php
                    // Formato para datetime-local: YYYY-MM-DDThh:mm
                    $nowAttr = (new \DateTime())->format('Y-m-d\TH:i');
                ?>
                <!-- Formulario para añadir al carrito -->
                        <form method="post" action="?page=cart" class="mt-4">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                            <input type="hidden" name="drone_id"   value="<?= $drone['id_drone'] ?>">
            
                            <div class="row g-3" style="margin-top: 5px;">
                                <div class="col-md-6">
                                    <label for="quantity" class="form-label">Cantidad</label>
                                    <input type="number" class="form-control" id="quantity" name="quantity" value="1" min="1" max="<?= (int)$drone['drone_stock'] ?>">
                                    <div class="form-text">
                                        Máximo disponible: <?= (int)$drone['drone_stock'] ?>
                                    </div>
                                </div>                                
                                
                                <div class="col-md-6">
                                    <label class="form-label">Tipo</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="is_rental" id="rentalOption" value="1" checked>
                                        <label class="form-check-label" for="rentalOption">Alquiler</label>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 rental-dates">
                                    <label for="start_date" class="form-label">Fecha de inicio</label>
                                    <input 
                                        type="datetime-local" 
                                        class="form-control" 
                                        id="start_date" 
                                        name="start_date" 
                                        min="<?= $nowAttr ?>"
                                        required>
                                </div>
                                
                                <div class="col-md-6 rental-dates">
                                    <label for="end_date" class="form-label">Fecha de fin</label>
                                    <input 
                                        type="datetime-local" 
                                        class="form-control" 
                                        id="end_date" 
                                        name="end_date"
                                        min="<?= $nowAttr ?>"
                                        required >
                                </div>
                                
                                <div class="col-12">
                                    <button type="submit" name="add_to_cart" class="btn btn-primary btn-lg w-100">
                                        <i class="bi bi-cart-plus me-2"></i> Añadir al carrito
                                    </button>
                                </div>
                            </div>
                        </form>
     </div>
 </div>

    <?php else: ?>
        <div class="alert alert-danger">Producto no encontrado</div>
        <a href="?page=catalog" class="btn btn-primary">Volver al catálogo</a>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>

<script>
// No se permite intentar alquilar más de los que hay disponibles.
document.getElementById('quantity').addEventListener('input', function() {
  const max = parseInt(this.max, 10);
  if (this.value > max) {
    this.value = max;
  }
  if (this.value < 1) {
    this.value = 1;
  }
});

// Mostrar/ocultar campos de fecha según el tipo seleccionado
document.addEventListener('DOMContentLoaded', function() {
    const rentalOption = document.getElementById('rentalOption');
    const purchaseOption = document.getElementById('purchaseOption');
    const rentalDates = document.querySelectorAll('.rental-dates');
    
    function toggleDateFields() {
        if (rentalOption.checked) {
            rentalDates.forEach(el => el.style.display = 'block');
        } else {
            rentalDates.forEach(el => el.style.display = 'none');
        }
    }
    
    // Inicializar estado
    toggleDateFields();
    
    // Escuchar cambios
    rentalOption.addEventListener('change', toggleDateFields);
    purchaseOption.addEventListener('change', toggleDateFields);
    
    // Establecer fechas mínimas
    const now = new Date();
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    
    // Formatear fecha para el input datetime-local
    const formatDateForInput = (date) => {
        return date.toISOString().slice(0, 16);
    };
    
    // Establecer valores mínimos
    startDateInput.min = formatDateForInput(now);
    endDateInput.min = formatDateForInput(new Date(now.getTime() + 3600000)); // 1 hora después
    
    // Actualizar mínimo de fecha fin cuando cambia fecha inicio
    startDateInput.addEventListener('change', function() {
        if (this.value) {
            endDateInput.min = this.value;
            if (!endDateInput.value || new Date(endDateInput.value) < new Date(this.value)) {
                const endDate = new Date(new Date(this.value).getTime() + 3600000); // 1 hora después
                endDateInput.value = formatDateForInput(new Date(endDate));
            }
        }
    });
});

</script>

<style>
.product-gallery {
    border: 1px solid #dee2e6;
    border-radius: 0.5rem;
    padding: 1rem;
}

.main-image img {
    max-height: 500px;
    width: 100%;
    object-fit: contain;
}

.rating {
    font-size: 1.2rem;
    letter-spacing: 0.2rem;
}

.list-group-item {
    padding: 0.75rem 0;
    background-color: transparent;
}

.rental-dates {
    display: block;
}
</style>