<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\ReportController as BaseReportController;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use App\Models\Invoice;
use Spatie\Permission\Models\Permission;

class ReportController extends BaseReportController
{
    use HasRoles;

    /**
     * Verifica si el usuario tiene el permiso especificado
     *
     * @param string $permission
     * @return bool
     */
    protected function hasPermissionTo($permission)
    {
        $user = Auth::user();
        Log::info('Verificando permiso', [
            'user_id' => $user->id,
            'permission' => $permission,
            'has_permission' => $user->hasPermissionTo($permission)
        ]);
        return $user->hasPermissionTo($permission);
    }

    /**
     * Verifica si el usuario tiene alguno de los permisos especificados
     *
     * @param array $permissions
     * @return bool
     */
    protected function hasAnyPermission($permissions)
    {
        $user = Auth::user();
        Log::info('Verificando permisos', [
            'user_id' => $user->id,
            'permissions' => $permissions,
            'has_any_permission' => $user->hasAnyPermission($permissions)
        ]);
        return $user->hasAnyPermission($permissions);
    }

    /**
     * Verifica si el usuario tiene acceso a la compañía especificada
     *
     * @param int $companyId
     * @return bool
     */
    protected function hasAccessToCompany($companyId)
    {
        $user = Auth::user();
        
        // Si el usuario es admin, tiene acceso a todas las compañías
        if ($user->hasRole('admin')) {
            return true;
        }

        // Verificar si el usuario pertenece a la compañía
        return $user->company_id == $companyId;
    }

    /**
     * Obtiene el reporte de ventas para una compañía específica.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getSalesReport(Request $request): JsonResponse
    {
        try {
            // Obtener el usuario autenticado
            $user = Auth::user();
            
            // Verificar si el usuario tiene una compañía asignada
            if (!$user->company_id) {
                Log::error('Usuario sin compañía asignada', [
                    'user_id' => $user->id,
                    'email' => $user->email
                ]);
            return response()->json([
                    'error' => 'No tienes una compañía asignada'
            ], 403);
        }
        
            // Verificar permisos del usuario
            if (!$user->hasPermissionTo('view_invoice') && !$user->hasPermissionTo('view_invoice_own')) {
                Log::error('Usuario sin permisos para ver reportes', [
                    'user_id' => $user->id,
                    'email' => $user->email
                ]);
            return response()->json([
                    'error' => 'No tienes permisos para ver reportes'
            ], 403);
        }

            // Obtener la compañía
            $company = Company::find($user->company_id);
            if (!$company) {
                Log::error('Compañía no encontrada', [
                    'user_id' => $user->id,
                    'company_id' => $user->company_id
                ]);
                return response()->json([
                    'error' => 'Compañía no encontrada'
                ], 404);
            }
            
            // Validar fechas
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            
            if (!$startDate || !$endDate) {
                return response()->json([
                    'error' => 'Las fechas de inicio y fin son requeridas'
                ], 422);
            }
            
            // Obtener los datos del reporte
            $reportData = $this->getReportData($company->id, $startDate, $endDate);
            
            return response()->json([
                'success' => true,
                'data' => $reportData
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al generar reporte de ventas', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Error al cargar los datos del reporte',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene los datos del reporte para una compañía específica.
     *
     * @param int $companyId ID del comercio
     * @param string $from Fecha inicial
     * @param string $to Fecha final
     * @param string $group Tipo de agrupación (day/hour)
     * @return array
     */
    public function getReportData($companyId, $from, $to, $group = 'day'): array
    {
        // Convertir fechas a objetos Carbon
        $start = Carbon::parse($from)->startOfDay();
        $end = Carbon::parse($to)->endOfDay();
        
        // Construir clave de caché
        $cacheKey = "sales_report_{$companyId}_{$from}_{$to}";
        
        // Intentar obtener datos del caché
        return Cache::remember($cacheKey, now()->addHours(1), function () use ($start, $end, $companyId) {
            // Consulta base de facturas para la compañía específica
            $query = Invoice::where('company_id', $companyId)
                ->whereBetween('issued_at', [$start, $end]);
            
            // Obtener datos agrupados por día
            $dailyData = $query->get()
                ->groupBy(function ($invoice) {
                    return $invoice->issued_at->format('Y-m-d');
                })
                ->map(function ($dayInvoices) {
                return [
                        'total_sales' => $dayInvoices->sum('total'),
                        'invoice_count' => $dayInvoices->count(),
                        'tax_total' => $dayInvoices->sum('tax'),
                ];
            });

            // Calcular totales
            $totals = [
                'total_sales' => $dailyData->sum('total_sales'),
                'invoice_count' => $dailyData->sum('invoice_count'),
                'tax_total' => $dailyData->sum('tax_total'),
            ];
            
            return [
                'daily_data' => $dailyData,
                'totals' => $totals,
            ];
        });
    }

    /**
     * Exporta el reporte de ventas a CSV.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportSalesReport(Request $request)
    {
        // Validar permisos
        if (!Auth::user()->hasPermissionTo('view_reports_basic')) {
            return response()->json([
                'error' => 'No tiene permisos para exportar reportes'
            ], 403);
        }
        
        // Verificar que el usuario tenga una compañía asignada
        if (!Auth::user()->company_id) {
            return response()->json([
                'error' => 'No tiene una compañía asignada'
            ], 400);
        }

        // Validar parámetros
        $validated = $request->validate([
            'from' => 'required|date',
            'to' => 'required|date',
            'group' => ['required', 'string', Rule::in(['day', 'hour'])]
        ]);

        try {
            return $this->exportReport(
                Auth::user()->company_id,
                $validated['from'],
                $validated['to'],
                $validated['group']
            );
        } catch (\Exception $e) {
            \Log::error('Error al exportar reporte de ventas: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'company_id' => Auth::user()->company_id,
                'from' => $validated['from'],
                'to' => $validated['to'],
                'group' => $validated['group']
            ]);

            return response()->json([
                'error' => 'Error al exportar el reporte. Por favor, intente nuevamente.'
            ], 500);
        }
    }
} 