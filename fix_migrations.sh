#!/bin/bash

# Script para reorganizar las migraciones en un orden correcto

# Preservar las migraciones básicas de Laravel
echo "Preservando migraciones base de Laravel..."
LARAVEL_MIGRATIONS=(
  "2014_10_12_000000_create_users_table.php"
  "2014_10_12_100000_create_password_reset_tokens_table.php"
  "2019_08_19_000000_create_failed_jobs_table.php"
  "2019_12_14_000001_create_personal_access_tokens_table.php"
)

# Lista de migraciones antiguas a eliminar
echo "Identificando migraciones antiguas para eliminar..."
MIGRATIONS_TO_DELETE=(
  "database/migrations/2023_05_23_100000_create_companies_table.php"
  "database/migrations/2023_05_23_110000_add_merchant_id_to_users_table.php"
  "database/migrations/2023_05_24_100000_create_invoices_table.php"
  "database/migrations/2023_05_25_000001_create_dian_resolutions_table.php"
  "database/migrations/2023_05_25_000002_remove_dian_fields_from_invoices_table.php"
  "database/migrations/2023_05_25_000003_remove_duplicated_dian_fields_from_invoices.php"
  "database/migrations/2025_04_23_042805_create_permission_tables.php"
  "database/migrations/2025_04_23_051733_create_products_table.php"
  "database/migrations/2025_04_23_054015_add_dian_status_to_invoices_table.php"
  "database/migrations/2025_04_23_133205_create_customers_table.php"
  "database/migrations/2025_04_24_020540_add_manage_products_permission.php"
  "database/migrations/2025_04_24_020626_create_product_price_history_table.php"
  "database/migrations/2025_04_24_021508_remove_sku_and_dian_code_from_products_table.php"
  "database/migrations/2025_04_24_041159_create_customers_table.php"
  "database/migrations/2025_04_25_014053_add_access_token_to_invoices_table.php"
  "database/migrations/2025_04_25_032824_rename_companies_to_companies.php"
  "database/migrations/2025_04_25_035704_create_invoice_details_table.php"
  "database/migrations/2025_04_25_042230_fix_company_relationships.php"
  "database/migrations/2025_04_25_132816_remove_dian_fields_from_invoices_table.php"
  "database/migrations/2025_04_25_144721_rename_merchant_id_to_company_id_in_invoices_table.php"
  "database/migrations/2025_04_25_144926_add_dian_fields_to_invoices_table.php"
)

# Eliminar migraciones antiguas (excluyendo migraciones base de Laravel)
echo "Eliminando migraciones antiguas..."
for migration in "${MIGRATIONS_TO_DELETE[@]}"; do
  if [ -f "$migration" ]; then
    echo "Eliminando $migration"
    rm "$migration"
  else
    echo "No se encontró $migration"
  fi
done

echo "¡Limpieza de migraciones completada!"
echo "Se han creado nuevas migraciones en orden lógico con timestamps del 2025."
echo "Por favor, ejecuta 'php artisan migrate:fresh' para aplicar el nuevo esquema." 