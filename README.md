# Dian - Proyecto Laravel con Sanctum y Spatie

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Sobre este proyecto

Este es un proyecto Laravel que utiliza Laravel Sanctum para autenticación API y Spatie Laravel Permission para la gestión de roles y permisos.

## Roles y Habilidades

El sistema implementa la siguiente matriz de roles y habilidades:

| Rol         | Abilities                                               |
| ----------- | ------------------------------------------------------- |
| Admin       | \* (todas las habilidades)                              |
| Comerciante | view_invoice, manage_products, view_reports_basic |
| Cliente     | view_invoice_own                                        |

### Descripción de Habilidades

-   **view_invoice**: Permite ver todas las facturas
-   **view_invoice_own**: Permite ver solo las facturas propias
-   **manage_products**: Permite gestionar productos (CRUD)
-   **view_reports_basic**: Permite acceder a reportes básicos
-   **manage_users**: Permite gestionar usuarios (solo Admin)
-   **manage_roles**: Permite gestionar roles y permisos (solo Admin)
-   **manage_companies**: Permite gestionar comerciantes (solo Admin)

## Instalación y Configuración

1. Clonar el repositorio
2. Ejecutar `composer install`
3. Copiar `.env.example` a `.env` y configurar la base de datos
4. Ejecutar `php artisan key:generate`
5. Ejecutar migraciones y seeders:
    ```bash
    php artisan migrate
    php artisan db:seed
    ```

## Usuarios por Defecto

El sistema crea los siguientes usuarios por defecto:

-   **Admin**: admin@ejemplo.com / password
-   **Comerciante**: comerciante@ejemplo.com / password
-   **Cliente**: cliente@ejemplo.com / password

## Autenticación API

Para autenticarse y obtener un token, enviar una solicitud POST a `/api/auth/token`:

```json
{
    "email": "admin@ejemplo.com",
    "password": "password",
    "device_name": "navegador"
}
```

El token devuelto contendrá las habilidades (abilities) según el rol del usuario.

## Paquetes instalados

-   Laravel Sanctum
-   Spatie Laravel Permission

## Testing

Para ejecutar los tests:

```bash
php artisan test
```

Para ejecutar específicamente los tests de habilidades:

```bash
php artisan test --filter=AbilityTest
```

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
