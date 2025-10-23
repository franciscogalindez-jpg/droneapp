<?php include 'includes/header.php'; ?>

<div class="login-page bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-sm-10 col-md-8 col-lg-8 col-xl-6">
                <div class="card shadow-lg border-0">
                    <div class="row g-0">
                        <!-- Imagen decorativa -->
                        <div class="col-md-5 d-none d-md-block login-image">
                            <div class="h-100 d-flex align-items-center justify-content-center p-4">
                                <img src="../assets/images/banners/drone-login.png" alt="Drone Login" class="img-fluid">
                                <div class="login-overlay-text text-white text-center">
                                    <h3>Bienvenido de vuelta</h3>
                                    <p class="small">Accede a tu cuenta para gestionar tus alquileres.</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Formulario -->
                        <div class="col-12 col-md-7"> 
                            <div class="card-body p-4 p-lg-5">
                                <div class="text-center mb-4">
                                    <img src="../assets/images/logo.png" alt="Logo" width="80" class="mb-3">
                                    <h2 class="fw-bold text-primary">Iniciar Sesión</h2>
                                    <p class="text-muted">Ingresa tus credenciales para continuar</p>
                                </div>
                                
                                <form action="?page=login" method="POST" class="needs-validation" novalidate>
                                    <!-- CSRF token -->
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Correo Electrónico</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                            <input type="email" class="form-control" id="email" name="email" 
                                                   placeholder="tucorreo@ejemplo.com" required>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="password" class="form-label">Contraseña</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                            <input type="password" class="form-control" id="password" name="password" 
                                                   placeholder="••••••••" required>
                                            <button class="btn btn-outline-secondary toggle-password" type="button">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                        <div class="form-text text-end">
                                            <a href="#" class="small">¿Olvidaste tu contraseña?</a>
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid mb-3">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="bi bi-box-arrow-in-right me-2"></i> Iniciar Sesión
                                        </button>
                                    </div>
                                    
                                    <div class="text-center">
                                        <p class="small text-muted">¿No tienes una cuenta? 
                                            <a href="?page=register" class="text-primary fw-bold">Regístrate aquí</a>
                                        </p>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.login-page {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    min-height: 100vh;
}

.login-image {
    background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
    position: relative;
    overflow: hidden;
}

.login-image img {
    max-height: 300px;
    z-index: 1;
    position: relative;
}

.login-overlay-text {
    position: absolute;
    bottom: 20px;
    left: 0;
    right: 0;
    padding: 0 20px;
    z-index: 1;
}

.card {
    border-radius: 15px;
    overflow: hidden;
}

.toggle-password {
    cursor: pointer;
}

/* Animación para el formulario */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.card-body {
    animation: fadeIn 0.5s ease-out;
}
</style>

<script>
// Mostrar/ocultar contraseña
document.querySelectorAll('.toggle-password').forEach(button => {
    button.addEventListener('click', function() {
        const passwordInput = this.closest('.input-group').querySelector('input');
        const icon = this.querySelector('i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    });
});

// Validación de formulario
document.querySelector('form.needs-validation').addEventListener('submit', function(event) {
    if (!this.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
    }
    this.classList.add('was-validated');
}, false);
</script>

<?php include 'includes/footer.php'; ?>