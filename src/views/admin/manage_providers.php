<?php include 'views/partials/header.php'; ?>

<div class="container">
    <h2><?= htmlspecialchars($pageTitle) ?></h2>
    
    <div class="mb-3">
        <a href="?page=admin&action=add_provider" class="btn btn-primary">Añadir Nuevo Proveedor</a>
    </div>
    
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Contacto</th>
                <th>Teléfono</th>
                <th>Drones Provistos</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($providers as $provider): ?>
            <tr>
                <td><?= htmlspecialchars($provider['id_proveedor']) ?></td>
                <td><?= htmlspecialchars($provider['nombre']) ?></td>
                <td><?= htmlspecialchars($provider['contacto']) ?></td>
                <td><?= htmlspecialchars($provider['telefono']) ?></td>
                <td><?= htmlspecialchars($provider['drones_provistos']) ?></td>
                <td>
                    <a href="?page=admin&action=edit_provider&id=<?= $provider['id_proveedor'] ?>" class="btn btn-sm btn-warning">Editar</a>
                    <a href="?page=admin&action=delete_provider&id=<?= $provider['id_proveedor'] ?>" class="btn btn-sm btn-danger">Eliminar</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'views/partials/footer.php'; ?>