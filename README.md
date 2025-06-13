## Cafe-Tienda

# Dia 1
se realizo un el sistema de login y registro de usuarios, el cual permite a los usuarios registrarse y luego iniciar sesión con sus credenciales. El sistema utiliza una base de datos para almacenar la información de los usuarios y se implementa una verificación de contraseña segura.
### Características
- Registro de usuarios con validación de datos.
- Inicio de sesión.

se realizo el apartado de index en donde se muestran productos y ubicacion de la cafeteria, ademas de un apartado de contacto para que los usuarios puedan comunicarse con la tienda.
### Características
- Visualización de productos.
- Mapa de ubicación de la cafetería.
- Formulario de contacto para consultas.


















# Requerimientos 

Información del Proyecto

Tipo: Sistema de información web
Metodología: Scrum (desarrollo ágil)
Tecnologías: PHP (PDO) y MySQL
Objetivo: Automatizar y digitalizar operaciones del negocio


REQUERIMIENTOS FUNCIONALES
1. Gestión de Inventario

RF-01: El sistema debe permitir el registro de productos y bebidas
RF-02: El sistema debe permitir la actualización de productos y bebidas
RF-03: El sistema debe controlar el stock (entradas y salidas)
RF-04: El sistema debe generar notificaciones de bajo inventario

2. Gestión de Pedidos

RF-05: El sistema debe permitir tomar pedidos desde dispositivos móviles
RF-06: La interfaz web debe ser adaptable para dispositivos móviles
RF-07: El sistema debe permitir la asignación de pedidos a mesas
RF-08: El sistema debe mostrar visualización en cocina o barra para preparación

3. Gestión de Ventas

RF-09: El sistema debe registrar automáticamente las ventas por pedido cerrado
RF-10: El sistema debe controlar ingresos por fecha, empleado y mesa
RF-11: El sistema debe generar automáticamente facturas en PDF por cada venta

4. Dashboard Administrativo

RF-12: El sistema debe mostrar visualización gráfica del recaudo mensual
RF-13: El sistema debe generar gráficos de productos más vendidos
RF-14: El sistema debe mostrar ingresos generados por cada empleado
RF-15: El sistema debe mostrar cantidad de mesas atendidas por mesero

5. Sistema de Reportes

RF-16: El sistema debe generar facturas individuales en PDF
RF-17: El sistema debe generar balance general de ventas entre fechas
RF-18: El sistema debe generar reporte de desempeño por empleado
RF-19: El sistema debe generar estado del inventario en PDF


REQUERIMIENTOS NO FUNCIONALES
Tecnológicos

RNF-01: El backend debe desarrollarse en PHP con PDO para conexión segura con base de datos
RNF-02: La base de datos debe ser MySQL
RNF-03: El frontend debe desarrollarse en HTML, CSS y JavaScript (básico o con frameworks)
RNF-04: El sistema debe ser compatible con navegadores web modernos

Rendimiento y Usabilidad

RNF-05: La interfaz debe ser responsiva para dispositivos móviles
RNF-06: El sistema debe generar documentos PDF (facturas, reportes)
RNF-07: El sistema debe incluir visualización gráfica usando Chart.js o librerías similares

Metodología

RNF-08: El desarrollo debe seguir metodología Scrum
RNF-09: Debe incluir sprints, reuniones diarias, backlog y roles definidos


ACTORES DEL SISTEMA
Usuario Principal

Meseros: Toman pedidos, asignan mesas
Cocineros/Baristas: Visualizan pedidos para preparación
Administrador: Accede a dashboard, reportes y gestión de inventario
Gerente: Consulta reportes de ventas y desempeño


MÓDULOS DEL SISTEMA

Módulo de Inventario
Módulo de Pedidos
Módulo de Ventas
Módulo de Dashboard Administrativo
Módulo de Reportes


CASOS DE USO PRINCIPALES

Registrar nuevo producto en inventario
Actualizar stock de productos
Tomar pedido desde dispositivo móvil
Asignar pedido a mesa
Procesar venta y generar factura
Consultar dashboard de ventas
Generar reportes en PDF
Visualizar estado del inventario