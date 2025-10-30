// Configuración global
const AppConfig = {
    apiBaseUrl: '/api',
    cart: {
      addItemUrl: '/cart/add',
      updateItemUrl: '/cart/update',
      removeItemUrl: '/cart/remove'
    },
    wishlist: {
      addUrl: '/wishlist/add',
      removeUrl: '/wishlist/remove'
    }
  };
  
  // Módulo para el carrusel
  const HeroCarousel = {
    init() {
      this.carousel = new bootstrap.Carousel('#heroCarousel', {
        interval: 5000,
        ride: 'carousel'
      });
      
      this.bindEvents();
    },
    
    bindEvents() {
      document.querySelectorAll('.carousel-control').forEach(control => {
        control.addEventListener('click', this.handleControlClick.bind(this));
      });
    },
    
    handleControlClick(e) {
      const action = e.currentTarget.dataset.bsSlide;
      if (action === 'prev') {
        this.carousel.prev();
      } else {
        this.carousel.next();
      }
    }
  };
  
  // Módulo para el catálogo
  const Catalog = {
    init() {
      this.bindSearch();
      this.bindSort();
      this.bindQuickView();
      this.bindAddToCart();
    },
    
    bindSearch() {
      const searchForm = document.getElementById('searchForm');
      if (searchForm) {
        searchForm.addEventListener('submit', (e) => {
          e.preventDefault();
          const query = searchForm.querySelector('input').value.trim();
          window.location.href = `?page=catalog&search=${encodeURIComponent(query)}`;
        });
      }
    },
    
    bindSort() {
      const sortSelect = document.getElementById('sortSelect');
      if (sortSelect) {
        sortSelect.addEventListener('change', () => {
          const sortValue = sortSelect.value;
          const url = new URL(window.location.href);
          url.searchParams.set('sort', sortValue);
          window.location.href = url.toString();
        });
      }
    },
    
    bindQuickView() {
      document.querySelectorAll('.quick-view').forEach(button => {
        button.addEventListener('click', this.showQuickView.bind(this));
      });
    },
    
    showQuickView(e) {
      const productId = e.currentTarget.dataset.productId;
    },
    
    bindAddToCart() {
      document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', this.addToCart.bind(this));
      });
    },
    
    addToCart(e) {
      e.preventDefault();
      const form = e.currentTarget.closest('form');
      const formData = new FormData(form);
      
      fetch(AppConfig.cart.addItemUrl, {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          this.showToast('Producto añadido al carrito', 'success');
        } else {
          this.showToast(data.message || 'Error al añadir al carrito', 'error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        this.showToast('Error de conexión', 'error');
      });
    },
    
    showToast(message, type = 'success') {
      // Implementar toast notifications
    }
  };
  
  // Inicialización cuando el DOM está listo
  document.addEventListener('DOMContentLoaded', () => {
    HeroCarousel.init();
    Catalog.init();
    
    // Inicializar tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(tooltipTriggerEl => {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });
  });