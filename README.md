# CRUD con Fetch + PHP OOP + MySQL (Guardar, Editar y Buscar productos)

**Universidad Tecnológica de Panamá**
Facultad de Ingeniería en Sistemas — Campus Víctor Levis Sasso
**Asignatura:** Desarrollo Web
**Instructora:** Ing. Irina Fong
**Grupo:** 1S3122 — II Semestre 2026
**Fecha de entrega:** 22 de noviembre de 2026

## Integrantes

| Estudiante | Correo institucional |
|---|---|
| Elizabeth Acevedo | elizabeth.acevedo@utp.ac.pa |
| _(nombre del compañero/a)_ | _(correo institucional)_ |

---

## 1. Descripción

Aplicación web que permite **guardar, editar y buscar productos** desde un formulario dinámico, sin recargar la página. El navegador envía los datos con la API **Fetch** y un backend en **PHP orientado a objetos** los procesa y los guarda en **MySQL** usando **PDO**. Cada respuesta del servidor es un JSON limpio y el resultado se muestra al usuario con **SweetAlert2**.

## 2. Tecnologías

- HTML5, JavaScript (Fetch API, FormData, async/await)
- Bootstrap 5 (diseño, botones y Alert)
- SweetAlert2 (alertas de éxito, error y advertencia)
- PHP 8 con POO (clases `DB` y `Producto`)
- MySQL con PDO (consultas preparadas)
- Servidor local WampServer, editor VS Code

## 3. Estructura de carpetas

```
crud_productos/
├── database.sql          # Crea productosdb y la tabla productos
├── index.html            # Formulario, búsqueda y tabla
├── script.js             # Fetch, FormData, switch en JS y SweetAlert2
├── registrar.php         # Controlador con switch($_POST['Accion'])
├── README.md
├── imagenes/             # Capturas usadas en este documento
└── Modelo/
    ├── conexion.php      # Clase DB (singleton)
    └── Productos.php     # Clase Producto
```

## 4. Base de datos

Base de datos `productosdb`, tabla `productos`:

```sql
CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL,
    producto VARCHAR(100) NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    cantidad INT NOT NULL
);
```

## 5. Instalación y ejecución

1. Copiar la carpeta `crud_productos` dentro de `www` (WampServer) o `htdocs` (XAMPP).
2. Iniciar Apache y MySQL.
3. En phpMyAdmin, ejecutar el contenido de `database.sql`.
4. Si MySQL tiene contraseña, ajustarla en `Modelo/conexion.php` (por defecto `root` sin clave).
5. Abrir `http://localhost/crud_productos/` en el navegador.

## 6. Cómo funciona

1. El usuario llena el formulario y pulsa **Registrar** o **Actualizar**.
2. `script.js` valida los campos en el cliente y arma un `FormData` con el campo oculto `Accion` (`Guardar` o `Modificar`).
3. `fetch('registrar.php')` envía los datos por POST.
4. `registrar.php` evalúa `$_POST['Accion']` con un **`switch`** (`Guardar`, `Modificar`, `Buscar`, `Listar`), valida en el servidor y llama a la clase `Producto`.
5. La clase `Producto` usa la clase `DB`, que ejecuta consultas preparadas con PDO (protección contra inyección SQL).
6. El servidor responde únicamente con JSON: `success`, `message`, `accion` y `errors`.
7. Otro **`switch`** en JavaScript interpreta `data.accion`, muestra SweetAlert2 y actualiza la tabla con `ListarProductos()`.

### Regla de negocio de la cantidad

- Al **guardar** un producto nuevo, la cantidad mínima es **1**, para que nazca con existencias.
- Al **modificar**, la cantidad mínima es **0**, para poder editar otros datos de un producto agotado.
- La regla se aplica en el HTML (`min`), en JavaScript y en PHP.

---

## 7. Evidencias de funcionamiento

### 7.1 Pantalla inicial

Formulario de registro, buscador y tabla, con diseño en Bootstrap.

![Pantalla inicial](imagenes/01_pantalla_inicial.png)

### 7.2 Guardar un producto

Al registrar un producto válido, el servidor responde `success: true` y SweetAlert2 muestra el mensaje de éxito.

![Guardar con éxito](imagenes/02_guardar_exito.png)

### 7.3 Validaciones en el cliente

Si faltan datos, los campos se marcan en rojo, aparece el Alert de Bootstrap con la lista de errores y SweetAlert2 avisa que hay que revisar el formulario.

![Validación de campos vacíos](imagenes/03_validacion_errores.png)

### 7.4 Validación de cantidad al guardar

Al registrar un producto nuevo con cantidad 0, se rechaza porque la cantidad mínima es 1.

![Cantidad 0 al guardar](imagenes/06_cantidad_cero_guardar.png)

### 7.5 Buscar

La búsqueda por código o nombre filtra la tabla (ejemplo: "carne").

![Búsqueda de productos](imagenes/04_buscar.png)

### 7.6 Editar y actualizar

El botón **Editar** carga los datos en el formulario, que cambia a modo "Editar producto" con el botón **Actualizar**. Al guardar, el servidor responde con éxito.

![Modificar con éxito](imagenes/05_modificar_exito.png)

### 7.7 Controlador `registrar.php`

Controlador central: la lógica Guardar / Modificar / Buscar / Listar está centralizada en un `switch`, y la salida es solo `json_encode`.

![Código de registrar.php](imagenes/07_codigo_registrar_php.png)

### 7.8 Datos en MySQL

Registros almacenados en la tabla `productos` de la base de datos `productosdb`, vistos desde phpMyAdmin.

![Tabla productos en phpMyAdmin](imagenes/08_phpmyadmin_productos.png)

---

## 8. Cumplimiento de la rúbrica

| Criterio | Pts | Dónde se cumple |
|---|---|---|
| Formulario realiza guardar, editar y buscar | 10 | `index.html` + `script.js` (secciones 7.2, 7.5, 7.6) |
| Bootstrap para el diseño y botones (Alert) | 10 | `index.html` con Bootstrap 5 y `alert alert-danger` (7.1, 7.3) |
| Lógica Guardar / Modificar centralizada con switch | 15 | `registrar.php` (7.7) |
| Switch también en JavaScript | 10 | `cambiarModo()` y `switch (data.accion)` en `script.js` |
| Clase DB implementada | 10 | `Modelo/conexion.php` (`insertSeguro`, `updateSeguro`, `query`) |
| Clase Producto implementada | 10 | `Modelo/Productos.php` (`guardar`, `editar`, `buscar`, `validar`) |
| Validaciones y control de errores (servidor y cliente) | 10 | `validar()` en PHP y `validarCliente()` en JS (7.3, 7.4) |
| Respuestas JSON con success, message, errors | 10 | Toda respuesta de `registrar.php` |
| SweetAlert2 para éxito y errores | 10 | `script.js` (7.2, 7.3, 7.6) |
| Código limpio, comentarios y estructura de carpetas | 5 | Sección 3 y comentarios en el código |

## 9. Conclusión

El proyecto integra Fetch API en el cliente con un backend PHP orientado a objetos y MySQL. La lógica del CRUD queda centralizada con `switch`, las consultas con PDO protegen contra inyección SQL y las validaciones en cliente y servidor garantizan la integridad de los datos.
