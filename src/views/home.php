<?php include 'includes/header.php'; ?>

<!-- Hero Section con video background -->
<section class="hero-section">
    <div class="video-background">
        <video autoplay muted loop playsinline>
            <source src="../assets/videos/d4.mp4" type="video/mp4">
            <source src="../assets/videos/drone-hero.webm" type="video/webm">
        </video>
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1 class="display-4 fw-bold">Explora el cielo con nuestros drones</h1>
            <p class="lead">Alquiler de drones profesionales para fotografía, videografía y diversión</p>
            <div class="d-flex gap-3 justify-content-center">
                <a href="?page=catalog" class="btn btn-primary btn-lg">Ver Catálogo</a>
                <a href="#how-it-works" class="btn btn-outline-light btn-lg">Cómo funciona</a>
            </div>
        </div>
    </div>
</section>

<!-- Sección "Cómo funciona" -->
<section id="how-it-works" class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">¿Cómo alquilar tu dron?</h2>
            <p class="lead text-muted">Solo 3 sencillos pasos</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center p-4">
                        <div class="step-number bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3">
                            1
                        </div>
                        <h4>Elige tu dron</h4>
                        <p class="text-muted">Selecciona entre nuestra amplia gama de drones profesionales y recreativos.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center p-4">
                        <div class="step-number bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3">
                            2
                        </div>
                        <h4>Selecciona fechas</h4>
                        <p class="text-muted">Indica cuándo necesitas el dron y por cuánto tiempo.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center p-4">
                        <div class="step-number bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3">
                            3
                        </div>
                        <h4>Recibe y disfruta</h4>
                        <p class="text-muted">Te lo llevamos directamente a tu casa. ¡A volar!</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Sección de estadísticas -->
<section class="py-5 bg-primary text-white">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-3 mb-4 mb-md-0">
                <div class="stat-item">
                    <h3 class="display-4 fw-bold"><?= $stats['total_products'] ?? '50+' ?></h3>
                    <p class="mb-0">Drones disponibles</p>
                </div>
            </div>
            <div class="col-md-3 mb-4 mb-md-0">
                <div class="stat-item">
                    <h3 class="display-4 fw-bold"><?= $stats['happy_customers'] ?? '500+' ?></h3>
                    <p class="mb-0">Clientes satisfechos</p>
                </div>
            </div>
            <div class="col-md-3 mb-4 mb-md-0">
                <div class="stat-item">
                    <h3 class="display-4 fw-bold">24/7</h3>
                    <p class="mb-0">Soporte técnico</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-item">
                    <h3 class="display-4 fw-bold">100%</h3>
                    <p class="mb-0">Garantía</p>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- Productos destacados con carrusel -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Nuestros Drones Destacados</h2>
            <p class="lead text-muted">Los modelos más populares</p>
        </div>
        
        <div id="featuredProductsCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php 
                $chunkedProducts = array_chunk($featuredProducts, 4); // 4 productos por slide
                foreach ($chunkedProducts as $index => $productsChunk): 
                ?>
                <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                    <div class="row g-4">
                        <?php foreach ($productsChunk as $product):
                            $title       = htmlspecialchars($product['name'] ?? 'Dron');
                            $desc        = htmlspecialchars($product['description'] ?? 'Sin descripción');
                            $imgSrc      = htmlspecialchars($product['drone_image'] ?? 'default-drone.jpg');
                            $dailyRate   = number_format($product['daily_rate'] ?? 0, 2);
                            $hourlyRate  = number_format($product['hourly_rate'] ?? 0, 2);    
                        ?>
                        <div class="col-md-3">
                            <div class="card product-card h-100">
                                <?php if (!empty($product['featured'])): ?>
                                <div class="badge bg-danger position-absolute top-0 end-0 m-2">Destacado</div>
                                <?php endif; ?>
                                
                                <div class="product-image-container">
                                    <img src="../assets/images/products/<?= $imgSrc ?>"
                                        class="card-img-top"
                                        alt="<?= $title ?>">
                                </div>
                                
                                <div class="card-body">
                                    <h5 class="card-title"><?= $title ?></h5>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-primary"><?= htmlspecialchars($product['category_name'] ?? '') ?></span>
                                        <div class="rating small text-warning">
                                            <i class="bi bi-star-fill"></i>
                                            <i class="bi bi-star-fill"></i>
                                            <i class="bi bi-star-fill"></i>
                                            <i class="bi bi-star-fill"></i>
                                            <i class="bi bi-star-half"></i>
                                        </div>
                                    </div>
                                    <p class="card-text text-muted small"><?= htmlspecialchars(substr($desc, 0, 80)) ?>...</p>
                                </div>
                                
                                <div class="card-footer bg-white border-0">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <h5 class="mb-0 text-primary">$<?= $dailyRate ?>/día</h5>
                                            <small class="text-muted">$<?= $hourlyRate ?>/hora</small>
                                        </div>
                                    </div>
                                    <a href="?page=product&id=<?= (int)$product['id_drone'] ?>" class="btn btn-outline-primary btn-sm w-100">
                                        Ver detalles
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <button class="carousel-control-prev" type="button" data-bs-target="#featuredProductsCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon bg-dark rounded-circle p-3" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#featuredProductsCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon bg-dark rounded-circle p-3" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </div>
</section>

<!-- Modal para vista rápida -->
<div class="modal fade" id="quickViewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Vista Rápida</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="quickViewContent">
                <!-- Contenido cargado por AJAX -->
            </div>
        </div>
    </div>
</div>

<style>

</style>

<?php include 'includes/footer.php'; ?>
<script src="assets/js/bootstrap.bundle.min.js"></script>