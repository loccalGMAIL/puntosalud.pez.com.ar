# 🏥 PuntoSalud - Sistema de Gestión Médica

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red?style=flat&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2-blue?style=flat&logo=php)](https://php.net)
[![Version](https://img.shields.io/badge/Version-2.4.2-green?style=flat)](#changelog)
[![License](https://img.shields.io/badge/License-MIT-yellow?style=flat)](#license)

Sistema integral de gestión médica para clínicas y consultorios, desarrollado con Laravel 12 y tecnologías modernas.

## 📋 Tabla de Contenidos

- [Características](#características)
- [Tecnologías](#tecnologías)
- [Comandos de Desarrollo](#comandos-de-desarrollo)
- [Arquitectura del Sistema](#arquitectura-del-sistema)
- [Changelog](#changelog)
- [Contribución](#contribución)

## ✨ Características

### 🎯 **Gestión de Turnos**
- Programación de citas médicas con validación de disponibilidad
- **Sistema dual de pagos anticipados** (individual y paquetes)
- Control de estados: programado → atendido → cobrado
- Asignación automática de pagos a turnos

### 💰 **Módulo de Pagos Avanzado** *(v2.1.0)*
- **Pagos individuales**: Un turno, un pago, ingreso inmediato
- **Paquetes de tratamiento**: Múltiples sesiones, un pago grupal  
- Métodos de pago: efectivo, transferencia, tarjeta
- Generación automática de números de recibo
- Trazabilidad completa de transacciones

### 🏦 **Gestión de Caja Integral** *(v2.4.0)*
- **Sistema completo de apertura/cierre de caja** con validaciones automáticas
- **Alertas inteligentes** para recepcionistas: caja sin cerrar, apertura pendiente
- **Trazabilidad completa** de todos los movimientos financieros por usuario
- **Control de estados**: verificación automática al login de recepcionistas
- **Traducción completa** de tipos de movimiento al español con iconos
- **Balance en tiempo real** con diferencias entre efectivo contado vs teórico
- Tipos de movimiento: apertura, cierre, pagos, gastos, entrega/recibo de turno
- Reportes diarios y por períodos personalizables

### 👨‍⚕️ **Administración de Profesionales**
- Gestión de especialidades médicas
- Configuración de comisiones por profesional
- Horarios de trabajo y excepciones
- Sistema de liquidaciones automático

### 👥 **Gestión de Pacientes**
- Registro completo de información personal y médica
- Historial de citas y tratamientos
- Seguimiento de pagos y saldos

### 📊 **Dashboard Optimizado** *(v2.0.0)*
- Vista en tiempo real del día actual
- Controles dinámicos de estado de turnos
- Resumen de ingresos por profesional y método de pago
- Interfaz responsiva con componentes reutilizables

### 📋 **Sistema de Reportes** *(v2.2.0)*
- **Listado de Pacientes a Atender**: Reporte diario para profesionales al llegar
- **Liquidación Diaria de Profesionales**: Reporte de cierre con comisiones calculadas
- Diferenciación de pagos anticipados vs. cobros del día
- Vista previa web y versión optimizada para impresión
- Auto-cierre de ventanas de impresión

## 🛠 Tecnologías

### Backend
- **Laravel 12** - Framework PHP
- **PHP 8.2** - Lenguaje de programación
- **MySQL** - Base de datos
- **Eloquent ORM** - Manejo de datos

### Frontend
- **Vite** - Build tool moderno
- **TailwindCSS 4.0** - Framework de CSS
- **Alpine.js** - Framework JavaScript reactivo
- **Blade** - Motor de plantillas

## ⚡ Comandos de Desarrollo

```bash
# Desarrollo completo (servidor + queue + vite)
composer dev

# Solo servidor Laravel
php artisan serve

# Solo desarrollo frontend
npm run dev

# Construir para producción
npm run build

# Ejecutar tests
composer test
php artisan test

# Formatear código
./vendor/bin/pint

# Limpiar caché
php artisan config:clear
```

## 🏗 Arquitectura del Sistema

### Modelos Principales
- **Appointment**: Gestión de citas médicas
- **Payment**: Sistema de pagos individual y paquetes
- **Professional**: Información y configuración de médicos
- **Patient**: Datos de pacientes
- **CashMovement**: Trazabilidad completa de caja

### Servicios
- **PaymentAllocationService**: Asignación automática de pagos a turnos
- Lógica de negocio centralizada para pagos y liquidaciones

### Base de Datos
- Migraciones con versionado temporal
- Relaciones Eloquent optimizadas
- Índices para consultas eficientes

## 📝 Changelog

### v2.4.2 (2025-09-23) - Sistema de Liquidaciones y Mejoras de UX
**🏦 Sistema de Liquidación de Profesionales:**
- **Botón de Liquidación**: Nuevo botón "Liquidar" en reportes de liquidación profesional
  - Disponible en vista de selección (`/reports/professional-liquidation`) y detalle
  - Solo visible cuando hay monto a liquidar (> 0)
  - Color distintivo naranja para diferenciarlo de otras acciones
- **Validaciones Avanzadas**: Control completo antes de liquidar
  - Verifica que no haya turnos sin atender del profesional
  - Verifica que no haya turnos atendidos sin cobrar
  - Valida saldo suficiente en caja y que esté abierta
  - Mensajes específicos indicando qué falta por completar
- **Movimientos de Caja**: Registro automático de pagos a profesionales
  - Tipo `professional_payment` con monto negativo
  - Referencia al profesional y usuario que procesa
  - Actualización automática del balance de caja

**🎨 Sistema de Modales Reutilizable:**
- **Componente Global**: Modal unificado para todo el sistema (`<x-system-modal>`)
  - Tipos: success, error, warning, confirm
  - Iconos y colores temáticos por tipo
  - Soporte para HTML en mensajes
- **JavaScript Global**: `SystemModal.confirm()` y `SystemModal.show()`
  - Reemplaza alerts nativos por interfaz profesional
  - Animaciones suaves y responsive design
  - Cierre con Escape y click fuera
- **UX Mejorada**: Confirmaciones elegantes para operaciones críticas
  - Liquidaciones con modal de confirmación
  - Mensajes de éxito y error consistentes
  - Mejor feedback visual para el usuario

**🔧 Correcciones y Mejoras Técnicas:**
- **Fix Detección de Caja Cerrada**: Corregido problema en `hasUnclosedCash()`
  - Cambio de `whereColumn()` a `whereRaw('DATE()')` para comparar fechas
  - Resuelve falsos positivos de "caja sin cerrar"
- **Gestión de Versiones**: Sistema de versionado mejorado
  - Versión leída desde archivo `version` en raíz del proyecto
  - Independiente del `.env` para mejor control en git
- **Comando Artisan**: Nuevo comando `php artisan cache:limpiar`
  - Limpia y regenera todas las cachés del sistema
  - Combina `optimize:clear`, `config:cache`, `route:cache`, `view:cache`

**🧭 Mejoras de Navegación:**
- **Breadcrumbs**: Agregados en vistas de reportes
  - `/reports/daily-schedule`: Dashboard > Reportes > Pacientes a Atender
  - `/reports/professional-liquidation`: Dashboard > Reportes > Liquidación Profesionales
- **Botones de Retorno**: Botón "Volver al Dashboard" en headers de reportes
  - Mejora la navegación con múltiples opciones de retorno
  - Diseño consistente con el sistema

**🎯 Limpieza de Código:**
- **Dashboard Simplificado**: Removidos botones de liquidación del dashboard principal
  - Interfaz más limpia y enfocada
  - Liquidaciones centralizadas en vistas específicas
- **Estados de Caja**: Eliminados mensajes permanentes de "caja abierta"
  - Solo muestra alertas cuando hay problemas que requieren acción
  - Interfaz menos intrusiva para operación normal

### v2.4.1 (2025-09-17) - Mejoras Avanzadas del Sistema de Caja
**🔧 Nuevas Funcionalidades de Caja:**
- **Visualización de Usuarios**: Los movimientos de caja ahora muestran el usuario responsable
  - Avatar circular con iniciales y nombre completo del usuario
  - Trazabilidad completa de quién genera cada ingreso/egreso
- **Reporte de Cierre Diario**: Nuevo reporte imprimible con resumen completo
  - Layout específico para impresión sin elementos de navegación
  - Desglose por tipo de movimiento y actividad por usuario
  - Estado de cierre con diferencias calculadas automáticamente
- **Retiro de Dinero**: Nueva funcionalidad para registrar salidas de efectivo
  - Formulario específico con tipos de retiro (depósito bancario, gastos, etc.)
  - Validación de saldo disponible antes de permitir retiros
  - Integración completa con el sistema de movimientos

**🛡️ Validaciones de Integridad:**
- **Control de Turnos vs Caja**: Los turnos requieren caja abierta para pagos inmediatos
  - Validación en backend: no permite turnos de hoy si caja cerrada
  - Validación de pagos: bloquea pagos inmediatos si caja no está operativa
  - Alertas visuales en agenda mostrando estado de caja en tiempo real
- **Consistencia Contable**: Previene inconsistencias entre turnos futuros y pagos presentes
  - Mensajes específicos según el contexto (turno hoy vs pago inmediato)
  - Opción de crear turno sin pago si la caja está cerrada

**🎨 Mejoras de Interfaz:**
- **Botón Condicional**: "Cerrar Caja" / "Reporte de Cierre" según estado
- **Modal de Cierre**: Interfaz intuitiva con resumen del día y detección de diferencias
- **Alertas Contextuales**: Estados de caja visibles en tiempo real (abierta/cerrada/sin abrir)
- **Flujo Automático**: Después del cierre redirige automáticamente al reporte generado

### v2.4.0 (2025-09-16) - Sistema Completo de Apertura/Cierre de Caja
**💰 Sistema de Gestión de Caja:**
- **Apertura/Cierre Automático**: Sistema completo de control de caja diario
  - Validación automática al login para recepcionistas
  - Alertas inteligentes: caja sin cerrar de día anterior, apertura pendiente
  - Registro del monto inicial y efectivo contado con diferencias
- **Trazabilidad por Usuario**: Seguimiento completo de quién abre/cierra la caja
  - Timestamps precisos y registro del usuario responsable
  - Control de estados: abierta, cerrada, necesita apertura
- **Modelos y Validaciones**: Lógica de negocio robusta
  - Nuevos scopes en CashMovement: `openingMovements()`, `closingMovements()`, `forDate()`
  - Métodos estáticos: `getCashStatusForDate()`, `hasUnclosedCash()`
  - Validaciones para prevenir múltiples aperturas/cierres del mismo día

**🎨 Interfaz de Usuario:**
- **Alertas Contextuales**: Banners informativos según estado de caja
  - 🔴 Rojo: Caja sin cerrar de día anterior (acción requerida)
  - 🟡 Amarillo: Necesita apertura del día actual
  - 🟢 Verde: Caja abierta correctamente con información del responsable
- **Modales Funcionales**: Formularios intuitivos para apertura/cierre
  - Validación de montos y campos opcionales para notas
  - Resumen automático con diferencias entre teórico vs contado
- **Traducción Completa**: Todos los tipos de movimiento en español
  - Iconos diferenciados por tipo: 🔓 Apertura, 🔒 Cierre, 💰 Pagos, etc.
  - Colores distintivos para identificación visual rápida

**🔧 Correcciones y Mejoras:**
- **Gestión de Pagos Anticipados**: Flujo corregido para evitar doble cobro
  - Pagos se crean al momento pero se asignan al atender el turno
  - Asignación automática de pagos al marcar turnos como atendidos
  - Actualización correcta de `final_amount` para dashboard y liquidaciones
- **Cálculo de Ingresos**: Dashboard corregido para mostrar ingresos reales del día
  - Basado en asignaciones de pago de turnos atendidos (no solo pagos creados)
  - Separación correcta por métodos de pago
- **Validaciones de Formularios**: Corrección de errores 422 en creación de turnos
  - Validación flexible de campos boolean y opcionales
  - Manejo mejorado de errores con logging para debug

**🚀 Nuevas Rutas y Controllers:**
- `GET /cash/status` - Verificar estado actual de caja
- `POST /cash/open` - Abrir caja con monto inicial
- `POST /cash/close` - Cerrar caja con conteo final

### v2.3.0 (2025-09-11) - Sistema de Autenticación y Control de Usuarios
**🔐 Nuevas Funcionalidades:**
- **Sistema de Autenticación Completo**: Login/logout con validación de credenciales
  - Pantalla de login moderna con imagen de fondo personalizada
  - Validación de usuarios activos y manejo de sesiones
  - Redirección automática según estado de autenticación
- **Gestión de Usuarios**: CRUD completo con control de permisos
  - Roles diferenciados: Administrador y Recepcionista
  - Activación/desactivación de usuarios
  - Políticas de autorización (UserPolicy)
- **Control de Acceso por Roles**: Sistema de permisos granular
  - Solo administradores pueden gestionar usuarios
  - Acceso diferenciado al menú de navegación
  - Protección de rutas sensibles
- **Middleware de Seguridad**: Verificación automática de usuarios activos
  - CheckUserActive middleware personalizado
  - Logout automático de usuarios desactivados
  - Protección de todas las rutas con middleware auth

**🎨 Mejoras de Interfaz:**
- **Pantalla de Login Rediseñada**: Diseño moderno de dos columnas
  - Panel izquierdo con imagen de fondo difuminada (back_login.png)
  - Gradientes verdes coherentes con la identidad visual
  - Información de marca y características del sistema
- **Menú de Usuario**: Dropdown con perfil y logout
  - Enlace a gestión de usuarios (solo admin)
  - Vista de perfil personal con cambio de contraseña
  - Navegación mejorada con breadcrumbs

**🏗️ Arquitectura y Seguridad:**
- **Modelos Expandidos**: User model con métodos de rol y scopes
- **Controladores Nuevos**: AuthController y UserController
- **Vistas Adicionales**: Login, gestión de usuarios, perfil
- **Seeders**: Usuarios por defecto (admin y recepcionista)
- **Rutas Protegidas**: Todas las rutas existentes requieren autenticación

**👤 Usuarios por Defecto:**
- Administrador: `admin@puntosalud.com` / `password123`
- Recepcionista: `recepcion@puntosalud.com` / `password123`

### v2.2.3 (2025-09-11) - Mejoras de UI y Experiencia de Usuario
**🎨 Mejoras de Interfaz:**
- **Dashboard optimizado**: Cards superiores reducidas para mejor aprovechamiento del espacio
  - Elementos más compactos sin perder legibilidad
  - Botones de reportes reubicados en línea con métricas principales
- **Favicon personalizado**: Nuevo diseño SVG representativo de PuntoSalud
  - Cruz médica con punto dorado distintivo y línea de pulso
- **Navegación breadcrumb**: Implementada en todas las vistas principales
  - Patrón consistente para mejor orientación del usuario
- **Títulos estandarizados**: Formato unificado "Sección - PuntoSalud"

**🔧 Mejoras de Contenido:**
- **Menú lateral**: "Pagos" → "Cobro Pacientes" (mayor claridad)
- **Estados de liquidación**: "Pendiente" → "Para liquidar" en vista de pagos
- **Eliminación de card innecesaria**: Removida "Profesionales Activos" del dashboard

### v2.2.2 (2025-09-11) - Corrección Sistema de Turnos
**🐛 Correcciones Críticas:**
- **Fix creación de turnos del mismo día**: Sistema ahora permite crear turnos para hoy con validación de horarios
  - Corrección en lógica de fechas pasadas: `isPast()` → `isBefore(today())`
  - Botón "+" aparece correctamente en el día actual
- **Validación completa de disponibilidad**: Sistema robusto que verifica:
  - Horarios laborales del profesional por día de semana
  - Conflictos con turnos existentes considerando duración
  - Días feriados y excepciones de horario
  - Fines de semana automáticamente bloqueados
- **Fix error 500 en creación de turnos**: Corrección de tipos de datos para Carbon
  - Conversión de `$duration` string a entero para `addMinutes()`
  - Aplicado en `store()`, `update()` y `availableSlots()`
- **Mejores mensajes de validación**: Mensajes personalizados en español
  - "Debe seleccionar un paciente" en lugar de mensajes genéricos
  - Información detallada de horarios disponibles vs solicitados

**🔧 Mejoras Técnicas:**
- Importación de modelos `ProfessionalSchedule` y `ScheduleException`
- Validación de horarios usando formato correcto (`H:i` vs objetos DateTime)
- Soporte para edición de turnos con exclusión del turno actual en validaciones
- Mensajes de error específicos con rangos horarios y motivos de rechazo

### v2.2.1 (2025-09-11) - Mejoras en Gestión de Pacientes
**🆕 Nuevas Funcionalidades:**
- **Sistema de activación/desactivación de pacientes**: Control completo del estado de pacientes
  - Campo `activo` en base de datos con valor por defecto `true`
  - Interfaz visual con botones de toggle activo/inactivo
  - Filtros por estado en la vista de pacientes

**🔧 Mejoras:**
- **Formateo automático de DNI**: Los DNI se formatean automáticamente con puntos
  - Entrada: `25678910` → Guardado: `25.678.910`
  - Manejo de DNI de 7 y 8 dígitos
  - Limpieza automática de espacios y puntos existentes
- **Corrección de fechas en edición**: Fix del formato de fecha de nacimiento en modal de edición
- **Corrección de estadísticas**: Arreglo en el conteo de pacientes sin obra social
  - Lógica simplificada: Total - Con obra social = Sin obra social

**🐛 Correcciones:**
- Fix en accessor `is_active` para compatibilidad entre frontend y backend
- Corrección en validación de campos `activo` vs `is_active`
- Mejora en la lógica de conteo de estadísticas de pacientes
- Formato correcto de fechas ISO para inputs HTML tipo date

### v2.2.0 (2025-08-30) - Sistema de Reportes para Profesionales
**🆕 Nuevas Funcionalidades:**
- **Listado de Pacientes a Atender**: Reporte diario imprimible para profesionales
  - Filtrado automático por profesional y fecha
  - Vista de selección con accesos directos
  - Información completa: horarios, pacientes, montos, estado de pagos
- **Liquidación Diaria de Profesionales**: Reporte de cierre con cálculos de comisiones
  - Separación de turnos por tipo de pago (anticipado, del día, pendiente)
  - Cálculo automático de comisiones por profesional
  - Resumen detallado de ingresos y liquidación
- **Sistema de impresión optimizado**: Auto-cierre de ventanas tras imprimir
- **Vistas de preview**: Visualización web antes de imprimir

**🔧 Mejoras:**
- Nuevos métodos en ReportController para manejo de reportes
- Vistas Blade optimizadas para impresión con CSS específico
- JavaScript para manejo automático de ventanas de impresión
- Integración completa con el dashboard principal

**🎯 Casos de Uso:**
- Profesional llega → imprime listado de pacientes del día
- Profesional se retira → imprime liquidación con sus comisiones

### v2.1.0 (2025-08-30) - Sistema Dual de Pagos Anticipados
**🆕 Nuevas Funcionalidades:**
- Sistema dual de pagos: individual y paquetes de tratamiento
- Pago anticipado al crear turnos con ingreso inmediato a caja
- Cálculo automático de totales para paquetes
- Modal mejorado con opciones flexibles de pago
- Extensión de tipos de movimientos de caja

**🔧 Mejoras:**
- Validaciones completas en frontend y backend
- JavaScript modernizado con ES6+ y async/await
- Componentes Blade reutilizables
- Manejo de errores robusto con transacciones DB

**🐛 Correcciones:**
- Fix en PaymentAllocationService para sesiones de paquetes
- Corrección de paths SVG malformados en dashboard
- Mejora en modal positioning y funcionalidad

### v2.0.0 (2025-08-28) - Dashboard y Módulo de Pagos
**🆕 Funcionalidades:**
- Dashboard completo para recepcionistas
- Módulo de pagos con liquidaciones automáticas
- Gestión de movimientos de caja
- Sistema de estados de turnos dinámico

### v1.0.0 (2025-07-03) - Versión Base
**🆕 Funcionalidades:**
- Gestión básica de turnos médicos
- CRUD de pacientes y profesionales
- Sistema de horarios y disponibilidad
- Interfaz base con Laravel 12

## 🤝 Contribución

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

### Convenciones
- Usar Laravel Pint para formateo de código
- Escribir tests para nuevas funcionalidades
- Documentar cambios en el changelog

## 📄 License

Este proyecto está bajo la licencia MIT. Ver [LICENSE](LICENSE) para más detalles.

---

**Desarrollado con ❤️ para el sector salud**

*Sistema en desarrollo activo - Contribuciones bienvenidas*