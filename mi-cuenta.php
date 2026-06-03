<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Cuenta - Reloop</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light px-4">
    <img src="./img/logo.png" class="logo me-2">
    <span class="brand-text">RE-LOOP</span>
</nav>

<div class="container py-5">

    <div class="row">

        <div class="col-lg-4 mb-4">

            <div class="card shadow border-0">

                <div class="card-body text-center">

                    <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png"
                        width="120">

                    <h4 id="nombreUsuario"></h4>

                    <p class="text-muted">
                        Cliente Reloop
                    </p>

                    <a href="index.html" class="btn btn-outline-primary mt-3">
                        Página Principal
                    </a>

                </div>

            </div>

        </div>

        <div class="col-lg-8">

            <div class="card shadow border-0">

                <div class="card-body p-4">

                    <h3 class="mb-4">
                        Información Personal
                    </h3>

                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label>Nombre</label>
                            <input id="nombre" type="text" class="form-control">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Apellidos</label>
                            <input id="apellidos" type="text" class="form-control">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Correo</label>
                            <input id="correo" type="email" class="form-control">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Teléfono</label>
                            <input id="telefono" type="text" class="form-control">
                        </div>

                    </div>

                    <button class="btn btn-success">
                        Guardar Cambios
                    </button>

                    <button id="btnLogout" class="btn btn-outline-danger ms-2">
                        Cerrar Sesión
                    </button>

                </div>

            </div>

            <div class="card shadow border-0 mt-4">

                <div class="card-body">

                    <h4>
                        Mis Pedidos
                    </h4>

                    <table class="table">

                        <thead>

                            <tr>
                                <th>Pedido</th>
                                <th>Fecha</th>
                                <th>Total</th>
                                <th>Estado</th>
                            </tr>

                        </thead>

                        <tbody id="tablaPedidos">
                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>

<script>

async function cargarUsuario()
{
    const respuesta =
        await fetch(
            "api/obtenerUsuario.php"
        );

    const usuario =
        await respuesta.json();

    if(usuario.success === false)
    {
        window.location =
            "login.html";

        return;
    }

    document
    .getElementById("nombreUsuario")
    .textContent =
        usuario.nombre;

    document
    .getElementById("nombre")
    .value =
        usuario.nombre;

    document
    .getElementById("apellidos")
    .value =
        usuario.apellidos;

    document
    .getElementById("correo")
    .value =
        usuario.correo;

    document
    .getElementById("telefono")
    .value =
        usuario.telefono;
}

cargarUsuario();

async function cargarPedidos()
{
    const respuesta =
        await fetch(
            "api/obtenerPedidos.php"
        );

    const pedidos =
        await respuesta.json();

    let html = "";

    if(pedidos.length === 0)
    {
        html = `
        <tr>
            <td colspan="4" class="text-center">
                Aún no tienes pedidos.
            </td>
        </tr>`;
    }
    else
    {
        pedidos.forEach(pedido => {

            let badge = "bg-warning";

            if(pedido.estado_pago === "Pagado")
            {
                badge = "bg-success";
            }

            html += `
            <tr>
                <td>#${pedido.id_pedido}</td>
                <td>${pedido.fecha_pedido}</td>
                <td>$${pedido.total_pedido}</td>
                <td>
                    <span class="badge ${badge}">
                        ${pedido.estado_pago}
                    </span>
                </td>
            </tr>`;
        });
    }

    document.getElementById("tablaPedidos").innerHTML = html;
}

cargarPedidos();

document
.getElementById("btnLogout")
.addEventListener(
"click",
async function()
{
    const respuesta =
        await fetch(
            "api/logout.php"
        );

    const datos =
        await respuesta.json();

    if(datos.success)
    {
        window.location =
            "login.html";
    }
});

</script>

</body>
</html>