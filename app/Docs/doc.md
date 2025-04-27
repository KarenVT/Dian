
 cumplir con los requerimientos tributarios establecidos por el Gobierno Nacional para que los pequeños empresarios, puedan generar facturación electrónica. (Este objetivo es el del proyecto en general). 
Contexto problémico para el desarrollo del proyecto (PMI): 
Desafío de la facturación electrónica para tiendas y pequeños negocios- Requerimiento de la DIAN. 
“En un contexto marcado por la digitalización y la modernización de los procesos tributarios, la facturación electrónica emerge como un componente fundamental en la gestión fiscal de las empresas en Colombia. partir del 1 de junio de 2023, las tirillas impresas por las máquinas registradoras, conocidas como tiquetes POS, serán válidas solo para ventas inferiores a 212.000 pesos. Si el monto supera esta cifra, los comerciantes deberán emitir facturas electrónicas. Esta medida, establecida en la Resolución 001092 del 1 de julio de 2022 por la DIAN, tiene como objetivo estandarizar procesos y simplificar la trazabilidad de las ventas. El incumplimiento de esta normativa puede resultar en multas y sanciones según lo establecido en el Estatuto Tributario2.” 
Es claro que el tema de la facturación electrónica puede ser tan oportunidad, como debilidad para muchos comerciantes, dado que aunque la Dian tiene ayudas didácticas, algunos software y capacitaciones, no todos los pequeños comerciantes saben cómo afrontar la obligación fiscal de facturar electrónicamente

Requisitos Funcionales
Código	Descripción del Requerimiento	Prioridad
RF-01	El sistema debe permitir el registro de comerciantes con datos verificados	Alta
RF-02	Los comerciantes deben poder generar facturas electrónicas con estructura válida	Alta
RF-03	El sistema debe almacenar y organizar facturas de acuerdo con la normativa DIAN	Alta
RF-04	Se debe permitir la consulta y descarga de facturas en formato PDF	Alta
RF-05	Los comerciantes deben poder gestionar productos y precios desde un panel de control	Media
RF-06	Se debe generar reportes detallados de ventas y facturación con filtros avanzados	Alta
Objetivo	El sistema debe cumplir con la normativa vigente de la DIAN	Priorizar
RF-07	Integración con la DIAN para validación y reporte automático de facturación	Alta
RF-08	Notificación automática de emisión de facturas a clientes por correo electrónico	Media
RF-09	Implementación de autenticación segura con roles de usuario diferenciados	Alta

A continuación, se presentan los diagramas de casos de uso que describen las principales interacciones entre los actores y el sistema de facturación electrónica:
 

 
Diagrama de clases.
Este diagrama de clases representa la estructura estática del sistema, mostrando las clases principales, sus atributos, métodos y relaciones.
Clases Principales:
Comerciante
+id: String
+nombre: String
+correo: String
+contraseña: String
+régimenTributario: String
Métodos: +registrar(), +editarPerfil(), +autenticar()
Factura
+id: String
+fechaEmisión: Date
+comercianteId: String
+cliente: Cliente
+productos: List<DetalleFactura>
+total: double
Métodos: +calcularTotal(), +generarPDF(), +enviarADIAN()
DetalleFactura
+producto: Producto
+cantidad: int
+precioUnitario: double
+subtotal: double
Métodos: +calcularSubtotal()
Producto
+id: String
+nombre: String
+precio: double
Métodos: +actualizarPrecio()
Cliente
+nombre: String
+identificación: String
+correo: String
DIAN
Métodos: +validarFactura(factura: Factura): Boolean

Relaciones:
•	Un Comerciante tiene muchos Productos.
•	Un Comerciante puede emitir muchas Facturas.
•	Una Factura tiene uno o más DetalleFactura.
•	Cada DetalleFactura contiene un Producto.


Los diagramas de secuencia muestran la interacción entre objetos del sistema a lo largo del tiempo, enfocándose en dos escenarios principales:

 1. Diagrama de Secuencia: Generar Factura
Comerciante -> Sistema : Inicia sesión
Comerciante -> Sistema : Registra datos del cliente y productos
Sistema -> Sistema : Calcula totales y valida datos
Sistema -> DIAN : Envía factura electrónica
DIAN -> Sistema : Confirma validación
Sistema -> Comerciante : Genera PDF y lo descarga
Sistema -> Cliente : Envía factura por correo






Los diagramas de secuencia muestran la interacción entre objetos del sistema a lo largo del tiempo, enfocándose en dos escenarios principales:
1. Diagrama de Secuencia: Generar Factura
Comerciante -> Sistema : Inicia sesión
Comerciante -> Sistema : Registra datos del cliente y productos
Sistema -> Sistema : Calcula totales y valida datos
Sistema -> DIAN : Envía factura electrónica
DIAN -> Sistema : Confirma validación
Sistema -> Comerciante : Genera PDF y lo descarga
Sistema -> Cliente : Envía factura por correo
f
 

2. Diagrama de Secuencia: Registrar Producto
Comerciante -> Sistema : Inicia sesión
Comerciante -> Sistema : Ingresa información del producto
Sistema -> Sistema : Valida y guarda producto en la base de datos
Sistema -> Comerciante : Confirma registro exitoso
 



