class Catalog {
    static init() {
      this.bindSearch();
      this.bindSorting();
      this.bindQuickView();
      this.bindWishlist();
      this.initLayoutToggle();
    }
  
    static bindSearch() {
      const searchForm = document.getElementById('searchForm');
      if (searchForm) {
        searchForm.addEventListener('submit', (e) => {
          e.preventDefault();
          const searchInput = searchForm.querySelector('input[name="search"]');
          this.updateUrlParams({ search: searchInput.value.trim(), page: 1 });
        });
      }
    }
  
    static bindSorting() {
      const sortSelect = document.getElementById('sortSelect');
      if (sortSelect) {
        sortSelect.addEventListener('change', () => {
          this.updateUrlParams({ sort: sortSelect.value, page: 1 });
        });
      }
    }
  
    static bindQuickView() {
      document.querySelectorAll('.quick-view').forEach(button => {
        button.addEventListener('click', () => {
          const productId = button.dataset.productId;
          this.showQuickView(productId);
        });
      });
    }
  
    static bindWishlist() {
      document.querySelectorAll('.add-to-wishlist').forEach(button => {
        button.addEventListener('click', async (e) => {
          e.preventDefault();
          const productId = button.dataset.productId;
          await this.toggleWishlist(productId, button);
        });
      });
    }
  
    static initLayoutToggle() {
      const layoutButtons = document.querySelectorAll('.layout-toggle button');
      const savedLayout = localStorage.getItem('productLayout') || 'grid';
      
      layoutButtons.forEach(button => {
        button.addEventListener('click', () => {
          const layout = button.dataset.layout;
          this.setActiveLayoutButton(button);
          this.toggleProductLayout(layout);
          localStorage.setItem('productLayout', layout);
        });
        
        if (button.dataset.layout === savedLayout) {
          button.classList.add('active');
        }
      });
      
      this.toggleProductLayout(savedLayout);
    }
  
    static updateUrlParams(params) {
      const url = new URL(window.location.href);
      Object.entries(params).forEach(([key, value]) => {
        if (value) url.searchParams.set(key, value);
        else url.searchParams.delete(key);
      });
      window.location.href = url.toString();
    }
  
    static setActiveLayoutButton(activeButton) {
      document.querySelectorAll('.layout-toggle button').forEach(btn => {
        btn.classList.remove('active');
      });
      activeButton.classList.add('active');
    }
  
    static toggleProductLayout(layout) {
      const productGrid = document.getElementById('product-grid');
      if (productGrid) {
        productGrid.classList.toggle('list-view', layout === 'list');
      }
    }
  
    static async showQuickView(productId) {
      const modal = new bootstrap.Modal(document.getElementById('quickViewModal'));
      const modalContent = document.getElementById('quickViewContent');
      
      try {
        modalContent.innerHTML = `
          <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Cargando...</span>
            </div>
          </div>
        `;
        
        modal.show();
        
        const response = await fetch(`/api/products/${productId}/quickview`);
        const product = await response.json();
        this.renderQuickViewContent(product, modalContent);
        
      } catch (error) {
        console.error('Error:', error);
        modalContent.innerHTML = `
          <div class="alert alert-danger">
            Error al cargar los detalles del producto.
          </div>
        `;
      }
    }
  
    static renderQuickViewContent(product, container) {
      container.innerHTML = `
        <div class="row g-4">
          <div class="col-md-6">
            <img src="${product.image}" alt="${product.name}" class="img-fluid rounded-3 mb-3">
            <div class="d-flex gap-2">
              <button class="btn btn-primary flex-grow-1 add-to-cart" data-product-id="${product.id}">
                <i class="bi bi-cart-plus me-2"></i> Añadir al carrito
              </button>
              <button class="btn btn-outline-secondary add-to-wishlist" data-product-id="${product.id}">
                <i class="bi bi-heart"></i>
              </button>
            </div>
          </div>
          <div class="col-md-6">
            <h3>${product.name}</h3>
            <div class="d-flex align-items-center mb-3">
              <div class="rating text-warning me-3">
                ${[1,2,3,4,5].map(i => 
                  `<i class="bi ${i <= product.rating ? 'bi-star-fill' : 
                    (i-0.5 <= product.rating ? 'bi-star-half' : 'bi-star')}"></i>`
                ).join('')}
              </div>
              <span class="text-muted small">${product.review_count} reseñas</span>
            </div>
            <div class="mb-4">
              <h4 class="text-primary">$${product.price} <small class="text-muted">/día</small></h4>
              <p class="text-muted">$${product.hourly_price} por hora</p>
            </div>
            <p class="mb-4">${product.description}</p>
            <div class="mb-4">
              <h5 class="h6 mb-3">Características:</h5>
              <ul class="list-unstyled">
                ${product.features.map(f => `<li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>${f}</li>`).join('')}
              </ul>
            </div>
          </div>
        </div>
      `;
      
      container.querySelector('.add-to-cart')?.addEventListener('click', () => this.addToCart(product.id));
      container.querySelector('.add-to-wishlist')?.addEventListener('click', (e) => {
        e.preventDefault();
        this.toggleWishlist(product.id, e.target.closest('button'));
      });
    }
  
    static async toggleWishlist(productId, button) {
      try {
        const isWishlisted = button.classList.contains('active');
        const method = isWishlisted ? 'DELETE' : 'POST';
        
        const response = await fetch(`/api/wishlist/${productId}`, {
          method,
          headers: { 'Content-Type': 'application/json' }
        });
        
        if (response.ok) {
          const icon = button.querySelector('i');
          button.classList.toggle('active');
          icon.classList.toggle('bi-heart');
          icon.classList.toggle('bi-heart-fill');
          icon.classList.toggle('text-danger');
          
          App.showNotification(
            isWishlisted ? 'Removido de tu lista' : 'Añadido a tu lista',
            isWishlisted ? 'info' : 'success'
          );
        }
      } catch (error) {
        console.error('Error:', error);
        App.showNotification('Error al actualizar', 'danger');
      }
    }
  
    static addToCart(productId) {
      // Implementar lógica para añadir al carrito
      App.showNotification('Producto añadido al carrito', 'success');
    }
  }
  
  document.addEventListener('DOMContentLoaded', () => Catalog.init());