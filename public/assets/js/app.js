class App {
    static showNotification(message, type = 'success') {
      const notification = document.createElement('div');
      notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
      notification.style.top = '20px';
      notification.style.right = '20px';
      notification.style.zIndex = '9999';
      notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      `;
      
      document.body.appendChild(notification);
      
      setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 150);
      }, 3000);
    }
  }
  
  class HomePage {
    static init() {
      this.initFeaturedProductsCarousel();
      this.bindQuickView();
      this.bindWishlist();
      this.bindAddToCart();
    }
  
    static initFeaturedProductsCarousel() {
      const carousel = document.getElementById('featuredProductsCarousel');
      if (carousel) {
        // Configuración adicional del carrusel si es necesaria
        new bootstrap.Carousel(carousel, {
          interval: 5000,
          touch: true,
          ride: 'carousel'
        });
      }
    }
  
    static bindQuickView() {
      document.addEventListener('click', (e) => {
        const quickViewBtn = e.target.closest('.quick-view');
        if (quickViewBtn) {
          e.preventDefault();
          const productId = quickViewBtn.dataset.productId;
          this.showQuickView(productId);
        }
      });
    }
  
    static bindWishlist() {
      document.addEventListener('click', (e) => {
        const wishlistBtn = e.target.closest('.add-to-wishlist');
        if (wishlistBtn) {
          e.preventDefault();
          const productId = wishlistBtn.dataset.productId;
          this.toggleWishlist(productId, wishlistBtn);
        }
      });
    }
  
    static bindAddToCart() {
      document.addEventListener('click', (e) => {
        const addToCartBtn = e.target.closest('.add-to-cart');
        if (addToCartBtn) {
          e.preventDefault();
          const productId = addToCartBtn.dataset.productId;
          this.addToCart(productId, addToCartBtn);
        }
      });
    }
  
    static async showQuickView(productId) {
      const modal = new bootstrap.Modal(document.getElementById('quickViewModal'));
      const modalContent = document.getElementById('quickViewContent');
      
      modalContent.innerHTML = `
        <div class="text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando...</span>
          </div>
        </div>
      `;
      
      modal.show();
      
      try {
        const response = await fetch(`api/products/${productId}/quickview`);
        if (!response.ok) throw new Error('Error al cargar el producto');
        
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
            <div class="product-image rounded-3 overflow-hidden mb-3">
              <img src="assets/images/products/${product.image}" alt="${product.name}" class="img-fluid w-100">
            </div>
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
              <h5 class="h6 mb-3">Características principales:</h5>
              <ul class="list-unstyled">
                ${product.features.map(feat => `<li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>${feat}</li>`).join('')}
              </ul>
            </div>
          </div>
        </div>
      `;
      
      container.querySelector('.add-to-cart')?.addEventListener('click', (e) => {
        e.preventDefault();
        const productId = e.currentTarget.dataset.productId;
        this.addToCart(productId, e.currentTarget);
      });
    }
  
    static async addToCart(productId, button) {
      if (!button) return;
      
      const originalHtml = button.innerHTML;
      button.innerHTML = '<i class="bi bi-arrow-repeat"></i>';
      button.disabled = true;
      
      try {
        const formData = new FormData();
        formData.append('add_to_cart', '1');
        formData.append('product_id', productId);
        formData.append('quantity', '1');
        
        const response = await fetch('?page=cart', {
          method: 'POST',
          body: formData
        });
        
        if (response.ok) {
          const result = await response.json();
          if (result.success) {
            button.innerHTML = '<i class="bi bi-check"></i>';
            button.classList.add('btn-success');
            App.showNotification(result.message, 'success');
            this.updateCartCounter(result.cart_count);
          } else {
            throw new Error(result.message);
          }
        } else {
          throw new Error('Error en la respuesta del servidor');
        }
      } catch (error) {
        console.error('Error:', error);
        button.innerHTML = originalHtml;
        App.showNotification(error.message || 'Error al añadir al carrito', 'danger');
      } finally {
        setTimeout(() => {
          button.innerHTML = originalHtml;
          button.classList.remove('btn-success');
          button.disabled = false;
        }, 2000);
      }
    }
  
    static updateCartCounter(count) {
      const cartCounter = document.querySelector('.cart-counter');
      if (cartCounter) {
        cartCounter.textContent = count;
        cartCounter.classList.add('animate__animated', 'animate__bounceIn');
        
        setTimeout(() => {
          cartCounter.classList.remove('animate__animated', 'animate__bounceIn');
        }, 1000);
      }
    }
  
    static toggleWishlist(productId, button) {
      const isWishlisted = button.classList.contains('active');
      
      button.classList.toggle('active');
      const icon = button.querySelector('i');
      icon.classList.toggle('bi-heart');
      icon.classList.toggle('bi-heart-fill');
      icon.classList.toggle('text-danger');
      
      App.showNotification(
        isWishlisted ? 'Removido de tu lista' : 'Añadido a tu lista',
        isWishlisted ? 'info' : 'success'
      );
    }
  }
  
  document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('#featuredProductsCarousel')) {
      HomePage.init();
    }
  });