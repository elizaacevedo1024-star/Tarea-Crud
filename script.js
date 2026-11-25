/**
 * script.js - Lógica del cliente.
 * Captura eventos, valida, arma FormData, llama a registrar.php con fetch
 * y muestra el resultado con SweetAlert2.
 */

const form = document.getElementById('formProducto');
const campoAccion = document.getElementById('Accion');
const campoId = document.getElementById('id');
const inputCantidad = document.getElementById('Cantidad');
const btnGuardar = document.getElementById('btnGuardar');
const btnCancelar = document.getElementById('btnCancelar');
const tituloForm = document.getElementById('tituloForm');
const alertaForm = document.getElementById('alertaForm');
const tabla = document.getElementById('tablaProductos');
const CAMPOS = ['Codigo', 'Producto', 'Precio', 'Cantidad'];

// ---------- Utilidades ----------

/** Escapa texto para evitar XSS al pintar la tabla */
function esc(texto) {
    const d = document.createElement('div');
    d.textContent = texto ?? '';
    return d.innerHTML;
}

/** Quita marcas de error del formulario */
function limpiarErrores() {
    CAMPOS.forEach(c => {
        document.getElementById(c).classList.remove('is-invalid');
        document.getElementById('err-' + c).textContent = '';
    });
    alertaForm.classList.add('d-none');
}

/** Muestra errores campo por campo (formato del JSON: errors) */
function mostrarErrores(errors) {
    const mensajes = [];
    Object.entries(errors || {}).forEach(([campo, msg]) => {
        const input = document.getElementById(campo);
        if (input) {
            input.classList.add('is-invalid');
            document.getElementById('err-' + campo).textContent = msg;
        }
        mensajes.push(msg);
    });
    if (mensajes.length) {
        alertaForm.innerHTML = mensajes.map(m => '• ' + esc(m)).join('<br>');
        alertaForm.classList.remove('d-none');
    }
}

/** Validación en el cliente antes de enviar */
function validarCliente(accion) {
    const errors = {};
    const codigo = document.getElementById('Codigo').value.trim();
    const producto = document.getElementById('Producto').value.trim();
    const precio = document.getElementById('Precio').value;
    const cantidad = inputCantidad.value;
    const minCant = accion === 'Guardar' ? 1 : 0;

    if (!codigo) errors.Codigo = 'El código es obligatorio.';
    if (!producto) errors.Producto = 'El nombre del producto es obligatorio.';
    if (precio === '' || isNaN(precio) || Number(precio) <= 0) errors.Precio = 'Ingrese un precio mayor que 0.';
    if (cantidad === '' || !Number.isInteger(Number(cantidad)) || Number(cantidad) < minCant) {
        errors.Cantidad = `La cantidad debe ser un entero mayor o igual a ${minCant}.`;
    }
    return errors;
}

// ---------- Modo del formulario (switch en JavaScript) ----------

/** Cambia el formulario entre modo "Guardar" y "Modificar" */
function cambiarModo(modo) {
    switch (modo) {
        case 'Modificar':
            campoAccion.value = 'Modificar';
            inputCantidad.min = '0'; // permite producto agotado
            btnGuardar.textContent = 'Actualizar';
            btnGuardar.classList.replace('btn-success', 'btn-warning');
            tituloForm.textContent = 'Editar producto';
            btnCancelar.style.display = 'inline-block';
            break;
        case 'Guardar':
        default:
            campoAccion.value = 'Guardar';
            campoId.value = '';
            inputCantidad.min = '1'; // producto nuevo nace con stock
            btnGuardar.textContent = 'Registrar';
            btnGuardar.classList.replace('btn-warning', 'btn-success');
            tituloForm.textContent = 'Registrar producto';
            btnCancelar.style.display = 'none';
            break;
    }
}

// ---------- Comunicación con el servidor ----------

/** Envía FormData a registrar.php y devuelve el JSON */
async function enviar(formData) {
    const resp = await fetch('registrar.php', { method: 'POST', body: formData });
    if (!resp.ok) throw new Error('Respuesta de red no válida (' + resp.status + ')');
    return await resp.json();
}

/** Pinta las filas de la tabla */
function pintarTabla(productos) {
    if (!productos.length) {
        tabla.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Sin resultados</td></tr>';
        return;
    }
    tabla.innerHTML = productos.map(p => `
        <tr>
            <td>${esc(p.id)}</td>
            <td>${esc(p.codigo)}</td>
            <td>${esc(p.producto)}</td>
            <td class="text-end">${Number(p.precio).toFixed(2)}</td>
            <td class="text-end">${esc(p.cantidad)}</td>
            <td><button class="btn btn-sm btn-outline-primary btn-editar"
                    data-id="${esc(p.id)}" data-codigo="${esc(p.codigo)}"
                    data-producto="${esc(p.producto)}" data-precio="${esc(p.precio)}"
                    data-cantidad="${esc(p.cantidad)}">Editar</button></td>
        </tr>`).join('');
}

/** Carga todos los productos en la tabla */
async function ListarProductos() {
    try {
        const fd = new FormData();
        fd.append('Accion', 'Listar');
        const data = await enviar(fd);
        if (data.success) pintarTabla(data.data);
        else Swal.fire('Error', data.message, 'error');
    } catch (error) {
        Swal.fire('Error', 'No se pudo cargar la lista: ' + error.message, 'error');
    }
}

// ---------- Eventos ----------

// Guardar / Modificar
form.addEventListener('submit', async (e) => {
    e.preventDefault();
    limpiarErrores();

    const accion = campoAccion.value; // "Guardar" o "Modificar"
    const errores = validarCliente(accion);
    if (Object.keys(errores).length) {
        mostrarErrores(errores);
        Swal.fire('Revise el formulario', 'Hay campos con errores.', 'warning');
        return;
    }

    try {
        const data = await enviar(new FormData(form));

        // switch en JS según la acción que responde el servidor
        switch (data.accion) {
            case 'Guardar':
            case 'Modificar':
                if (data.success) {
                    await Swal.fire('¡Éxito!', data.message, 'success');
                    form.reset();
                    cambiarModo('Guardar');
                    ListarProductos();
                } else {
                    mostrarErrores(data.errors);
                    Swal.fire('Error', data.message, 'error');
                }
                break;
            default:
                Swal.fire('Error', data.message || 'Respuesta desconocida.', 'error');
        }
    } catch (error) {
        Swal.fire('Error', 'Fallo en la petición: ' + error.message, 'error');
    }
});

// Buscar
document.getElementById('formBuscar').addEventListener('submit', async (e) => {
    e.preventDefault();
    try {
        const fd = new FormData();
        fd.append('Accion', 'Buscar');
        fd.append('termino', document.getElementById('termino').value.trim());
        const data = await enviar(fd);
        if (data.success) {
            pintarTabla(data.data);
            if (!data.data.length) Swal.fire('Sin resultados', 'No se encontraron productos.', 'info');
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    } catch (error) {
        Swal.fire('Error', 'Fallo en la búsqueda: ' + error.message, 'error');
    }
});

// Ver todos
document.getElementById('btnLimpiarBusqueda').addEventListener('click', () => {
    document.getElementById('termino').value = '';
    ListarProductos();
});

// Editar (delegación de eventos sobre la tabla)
tabla.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-editar');
    if (!btn) return;
    limpiarErrores();
    campoId.value = btn.dataset.id;
    document.getElementById('Codigo').value = btn.dataset.codigo;
    document.getElementById('Producto').value = btn.dataset.producto;
    document.getElementById('Precio').value = btn.dataset.precio;
    inputCantidad.value = btn.dataset.cantidad;
    cambiarModo('Modificar');
    window.scrollTo({ top: 0, behavior: 'smooth' });
});

// Cancelar edición
btnCancelar.addEventListener('click', () => {
    form.reset();
    limpiarErrores();
    cambiarModo('Guardar');
});

// Al cargar la página
ListarProductos();
