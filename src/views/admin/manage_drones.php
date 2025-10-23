<?php include 'views/partials/header.php'; ?>

<div class="container">
    <h2><?= htmlspecialchars($pageTitle) ?></h2>
    
    <div class="mb-3">
        <a href="?page=admin&action=add_drone" class="btn btn-primary">Añadir Nuevo Drone</a>
    </div>
    
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Marca</th>
                <th>Tipo</th>
                <th>Estado</th>
                <th>Veces Alquilado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($drones as $drone): ?>
            <tr>
                <td><?= htmlspecialchars($drone['id_dron']) ?></td>
                <td><?= htmlspecialchars($drone['nombre']) ?></td>
                <td><?= htmlspecialchars($drone['marca_nombre']) ?></td>
                <td><?= htmlspecialchars($drone['tipo_nombre']) ?></td>
                <td><?= htmlspecialchars($drone['estado']) ?></td>
                <td><?= htmlspecialchars($drone['veces_alquilado']) ?></td>
                <td>
                    <a href="?page=admin&action=edit_drone&id=<?= $drone['id_dron'] ?>" class="btn btn-sm btn-warning">Editar</a>
                    <a href="?page=admin&action=delete_drone&id=<?= $drone['id_dron'] ?>" class="btn btn-sm btn-danger">Eliminar</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'views/partials/footer.php'; ?>