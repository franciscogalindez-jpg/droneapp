<?php include 'includes/header.php'; ?>

<div class="register-page bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <img src="assets/images/logo.png" alt="Logo" width="60" class="mb-3">
                            <h2 class="fw-bold text-primary">Crear una cuenta</h2>
                            <p class="text-muted">Completa el formulario para registrarte</p>
                        </div>
                        
                        <form action="?page=register" method="POST" class="needs-validation" novalidate>
                            <!-- CSRF token -->
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="card_id" class="form-label">Cédula</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                                        <input type="text" class="form-control" id="card_id" name="card_id"
                                               placeholder="123456789012" pattern="[0-9]{12}"
                                               title="12 dígitos numéricos" required>
                                    </div>
                                    <div class="invalid-feedback">
                                        Por favor ingresa una cédula válida (12 dígitos)
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="username" class="form-label">Nombre Completo</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                                        <input type="text" class="form-control" id="username" name="username" required>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <label for="email" class="form-label">Correo Electrónico</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="password" class="form-label">Contraseña</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                        <input type="password" class="form-control" id="password" name="password"
                                               pattern="(?=.*\d)(?=.*[A-Z])(?=.*[!@#$%^&*]).{8,10}" required>
                                        <button class="btn btn-outline-secondary toggle-password" type="button">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">
                                        8-10 caracteres, incluyendo 1 mayúscula, 1 número y 1 símbolo
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="confirm_password" class="form-label">Confirmar Contraseña</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                        <input type="password" class="form-control" id="confirm_password"
                                               name="confirm_password" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Teléfono</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                        <input type="tel" class="form-control" id="phone" name="phone"
                                               pattern="\+?[0-9]+" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="gender_id" class="form-label">Género</label>
                                    <select class="form-select" id="gender_id" name="gender_id" required>
                                        <option value="" selected disabled>Seleccionar...</option>
                                        <option value="1">Masculino</option>
                                        <option value="2">Femenino</option>
                                        <option value="3">Otro</option>
                                    </select>
                                </div>
                                
                                <div class="col-12">
                                    <label for="address" class="form-label">Dirección</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-house"></i></span>
                                        <input type="text" class="form-control" id="address" name="address">
                                    </div>
                                </div>
                                
                                <div class="col-12 mt-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="terms" required>
                                        <label class="form-check-label" for="terms">
                                            Acepto los <a href="#" class="text-primary">Términos y Condiciones</a>
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="col-12 mt-3">
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="bi bi-person-plus me-2"></i> Registrarse
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="col-12 text-center mt-3">
                                    <p class="text-muted small">¿Ya tienes una cuenta? 
                                        <a href="?page=login" class="text-primary fw-bold">Inicia sesión aquí</a>
                                    </p>
                                </div>
                            </div>
                        </form>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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

// Validación de contraseña
const password = document.getElementById('password');
const confirm_password = document.getElementById('confirm_password');

function validatePassword() {
    if (password.value !== confirm_password.value) {
        confirm_password.setCustomValidity("Las contraseñas no coinciden");
    } else {
        confirm_password.setCustomValidity('');
    }
}

password.onchange = validatePassword;
confirm_password.onkeyup = validatePassword;

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
