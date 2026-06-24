# Sistema de Gestión de Pedidos - API REST (Laravel)

Este proyecto consiste en una API REST robusta y optimizada para la gestión de pedidos e inventario de productos. El sistema ha sido diseñado aplicando principios de arquitectura limpia, patrones de diseño nativos de Laravel y un enfoque estricto en la integridad y el rendimiento de los datos.

---

## Características Clave e Implementación Técnica

### 1 Transacciones Atómicas
El proceso de creación de pedidos está completamente blindado dentro de transacciones de base de datos (`DB::beginTransaction()`). Si ocurre cualquier imprevisto durante el flujo (por ejemplo, stock insuficiente o falla de servidor), el sistema ejecuta un `DB::rollBack()`, garantizando la consistencia absoluta del inventario.

### 2. Abstracción de Cálculos con Model Observers
Para mantener los controladores limpios (*Lean Controllers*), el cálculo del coste total del pedido y sus subtotales se gestiona de forma automática mediante un **`OrderObserver`** acoplado al ciclo de vida del modelo.

### 3. Gestión de Inventario mediante Eventos y Listeners
Al procesarse un pedido con éxito, se dispara el evento `OrderCreated`. Un `Listener` dedicado se encarga de verificar y descontar de manera aislada las unidades correspondientes del inventario de cada producto.

### 4. Seguridad Temprana (Middleware Personalizado)
Se implementó el middleware `CheckOrderOwner` para interceptar de manera temprana las peticiones de consulta o modificación de pedidos. Valida si el usuario autenticado es el verdadero propietario del recurso, respondiendo con un `403 Forbidden` antes de sobrecargar la base de datos.

### 5. Sistema de Caché Optimizado
* **Productos:** Listado cacheado globalmente por 5 minutos (`300` segundos) para reducir lecturas redundantes en la base de datos.
* **Pedidos:** Listado de pedidos pendientes cacheado de forma dinámica y única por ID de usuario (`user.{id}.orders.pending`), invalidándose de forma automática tras mutaciones (`store`, `cancel`).

---

## Instalación y Configuración con Laravel Sail (Docker)

Sigue estos pasos para levantar el entorno utilizando contenedores distribuidos:

1. **Clonar el repositorio:**
   ```bash
   git clone [https://github.com/kheredia04/SGPedidos.git](https://github.com/kheredia04/SGPedidos.git)
   cd SGPedidos

## Listado de Endpoints para Pruebas

La API cuenta con los siguientes endpoints estructurados. Todos los endpoints de órdenes requieren autenticación mediante un Bearer Token generado en el login/registro.

### Autenticación
| Método | Endpoint | Descripción | Auth |
| :--- | :--- | :--- | :--- |
| `POST` | `/api/auth/register` | Registro de un nuevo usuario | No |
| `POST` | `/api/auth/login` | Inicio de sesión (Retorna el Bearer Token) | No |

### Productos
| Método | Endpoint | Descripción | Auth | Cache |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | `/api/products` | Listar todos los productos disponibles | Sí | ⚡ Sí (5 min) |

### Órdenes / Pedidos
| Método | Endpoint | Descripción | Auth | Middleware / Reglas |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | `/api/orders` | Listar pedidos pendientes del usuario | Sí | ⚡ Filtra por scope `pending()` e implementa caché por usuario. |
| `POST` | `/api/orders` | Crear un nuevo pedido con productos | Sí | Transacción DB atómica, descuento de stock y cálculo de total automático. |
| `POST` | `/api/orders/{id}/cancel` | Cancelar un pedido en estado pendiente | Sí | Middleware `CheckOrderOwner` (403) y validación de estado (422/404). |

---

### 📂 Colección de Postman Incluida

Para facilitar y agilizar la revisión del proyecto, se ha adjuntado la colección completa con entornos y payloads listos para usar:

* **Archivo:** `orders_api_collection.json` (Ubicado en la raíz de este repositorio).
* **Cómo usarla:**
  1. Abre Postman.
  2. Haz clic en el botón **Import** (arriba a la izquierda) y selecciona el archivo `orders_api_collection.json`.
  3. Asegúrate de configurar la variable de entorno `{{gestionpedidos}}` con la URL local de tu servidor (por ejemplo, `http://localhost` si usas Laravel Sail).
  4. En las peticiones de la carpeta de Órdenes, el token de autorización ya está vinculado dinámicamente si utilizas el flujo de login.
