<?php include 'includes/header.php'; ?>

<main class="catalog-page">
    <div class="container py-5">
        <!-- Toolbar -->
        <div class="catalog-toolbar mb-4">
            <div class="row g-3 align-items-center">
                <div class="col-md-6">
                    <h1 class="h2 mb-0"><?= htmlspecialchars($pageTitle) ?></h1>
                    <?php if (!empty($searchQuery)): ?>
                    <p class="text-muted mb-0">Resultados para: "<?= htmlspecialchars($searchQuery) ?>"</p>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <div class="d-flex flex-wrap justify-content-md-end gap-3 align-items-center">
                        <form id="searchForm" class="flex-grow-1">
                            <div class="input-group">
                                <input type="text" class="form-control" 
                                       name="search" placeholder="Buscar drones..." 
                                       value="<?= htmlspecialchars($searchQuery ?? '') ?>">
                                <button class="btn btn-primary" type="submit">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </form>
                        <div class="sort-filter">
                            <select id="sortSelect" class="form-select">
                                <option value="name_asc" <?= $sortOption === 'name_asc' ? 'selected' : '' ?>>Ordenar por: Nombre (A-Z)</option>
                                <option value="name_desc" <?= $sortOption === 'name_desc' ? 'selected' : '' ?>>Nombre (Z-A)</option>
                                <option value="price_asc" <?= $sortOption === 'price_asc' ? 'selected' : '' ?>>Precio: Menor a Mayor</option>
                                <option value="price_desc" <?= $sortOption === 'price_desc' ? 'selected' : '' ?>>Precio: Mayor a Menor</option>
                                <option value="featured" <?= $sortOption === 'featured' ? 'selected' : '' ?>>Destacados Primero</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Sidebar -->
            <aside class="col-lg-3 mb-4 mb-lg-0">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-filter-left me-2"></i>Filtros</h5>
                    </div>
                    <div class="card-body">
                        <h6 class="mb-3">Categorías</h6>
                        <ul class="nav flex-column">
                            <li class="nav-item mb-2">
                                <a href="?page=catalog" class="nav-link <?= !$categoryId ? 'active' : '' ?>">
                                    <i class="bi bi-grid-fill me-2"></i> Todas las categorías
                                    <span class="badge bg-primary float-end">
                                        <?= array_sum(array_column($categories, 'drone_count')) ?>
                                    </span>
                                </a>
                            </li>
                            <?php foreach ($categories as $category): ?>
                            <li class="nav-item mb-2">
                                <a href="?page=catalog&category=<?= $category['id_category'] ?>" 
                                   class="nav-link <?= $categoryId == $category['id_category'] ? 'active' : '' ?>">
                                    <i class="bi bi-<?= $this->getCategoryIcon($category['name']) ?> me-2"></i>
                                    <?= htmlspecialchars($category['name']) ?>
                                    <span class="badge bg-primary float-end"><?= $category['drone_count'] ?></span>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </aside>

            <!-- Main Content -->
            <div class="col-lg-9">
                <?php if (empty($products)): ?>
                    <div class="card shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-drone text-muted" style="font-size: 3rem;"></i>
                            <h3 class="mt-3">No se encontraron drones</h3>
                            <p class="text-muted mb-4">Intenta ajustar tus filtros de búsqueda</p>
                            <a href="?page=catalog" class="btn btn-primary px-4">
                                <i class="bi bi-arrow-left me-2"></i>Volver al catálogo
                            </a>
                        </div>
                    </div>
                <?php else: 
                    $drones      = $products['items'];
                    $totalPages  = $products['totalPages'];
                    $currentPage = $products['currentPage'];
                ?>
                    <div class="row g-4">
                        
                        <?php foreach ($drones as $drone): ?>                    
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 product-card">
                                
                                <img src="../assets/images/products/<?= htmlspecialchars($drone['drone_image'] ?? 'default-drone.jpg') ?>" 
                                     class="card-img-top p-3" 
                                     alt="<?= htmlspecialchars($drone['name']) ?>"
                                     style="height: 200px; object-fit: contain;">
                                
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($drone['name']) ?></h5>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-primary"><?= htmlspecialchars($drone['category_name']) ?></span>
                                        <div class="rating small text-warning">
                                            <i class="bi bi-star-fill"></i>
                                            <i class="bi bi-star-fill"></i>
                                            <i class="bi bi-star-fill"></i>
                                            <i class="bi bi-star-fill"></i>
                                            <i class="bi bi-star-half"></i>
                                        </div>
                                    </div>
                                    <p class="card-text text-muted small"><?= htmlspecialchars(substr($drone['description'], 0, 100)) ?>...</p>
                                </div>
                                
                                <div class="card-footer bg-white border-0">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <h5 class="mb-0 text-primary">$<?= number_format($drone['daily_rate'], 2) ?>/día</h5>
                                            <small class="text-muted">$<?= number_format($drone['hourly_rate'], 2) ?>/hora</small>
                                        </div>
                                        <form method="post" action="?page=cart" class="d-inline">
                                            <input type="hidden" name="product_id" value="<?= $drone['id_drone'] ?>">
                                            <input type="hidden" name="quantity" value="1">                                        
                                        </form>
                                    </div>
                                    <a href="?page=product&id=<?= $drone['id_drone'] ?>" class="btn btn-outline-primary btn-sm w-100">
                                        Ver detalles
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                    <nav class="mt-5">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?= $currentPage == 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= $this->buildPaginationUrl($currentPage - 1) ?>">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>
                            
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= $this->buildPaginationUrl($i) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <li class="page-item <?= $currentPage == $totalPages ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= $this->buildPaginationUrl($currentPage + 1) ?>">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php 
function buildPaginationUrl($page) {
    $url = '?page=catalog';
    if (!empty($_GET['category'])) $url .= '&category=' . $_GET['category'];
    if (!empty($_GET['search'])) $url .= '&search=' . urlencode($_GET['search']);
    if (!empty($_GET['sort'])) $url .= '&sort=' . $_GET['sort'];
    return $url . '&page=' . $page;
}
?>

<?php include 'includes/footer.php'; ?>

<style>
.catalog-page {
    background-color: #f8f9fa;
    min-height: calc(100vh - 150px);
}

.catalog-toolbar {
    background-color: white;
    padding: 1.5rem;
    border-radius: 0.5rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.product-card {
    transition: all 0.3s ease;
    border: 1px solid rgba(0,0,0,0.1);
    height: 100%;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.card-img-top {
    height: 200px;
    object-fit: contain;
}

.page-item.active .page-link {
    background-color: #4361ee;
    border-color: #4361ee;
}

.page-link {
    color: #4361ee;
}
</style>

<script>
// Manejar búsqueda y ordenamiento
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('searchForm');
    const sortSelect = document.getElementById('sortSelect');
    
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const searchInput = this.querySelector('input[name="search"]');
            updateUrlParams({ search: searchInput.value.trim(), page: 1 });
        });
    }
    
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            updateUrlParams({ sort: this.value, page: 1 });
        });
    }
    
    function updateUrlParams(params) {
        const url = new URL(window.location.href);
        
        Object.entries(params).forEach(([key, value]) => {
            if (value !== undefined && value !== '') {
                url.searchParams.set(key, value);
            } else {
                url.searchParams.delete(key);
            }
        });
        
        window.location.href = url.toString();
    }
});
</script>