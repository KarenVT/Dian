<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    /**
     * Obtiene el reporte de ventas con filtros de fecha y agrupación.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sales(Request $request)
    {
        // Validar los parámetros de entrada
        $request->validate([
            'from' => 'required|date_format:Y-m-d',
            'to' => 'required|date_format:Y-m-d|after_or_equal:from',
            'group' => ['required', Rule::in(['day', 'hour'])],
        ]);

        // Obtener el comercio autenticado
        $merchant = $request->user()->merchant;
        $merchantId = $merchant->id;

        // Convertir fechas a objetos Carbon
        $fromDate = Carbon::parse($request->from)->startOfDay();
        $toDate = Carbon::parse($request->to)->endOfDay();

        // Clave única para el caché
        $cacheKey = "sales_report_{$merchantId}_{$fromDate->format('Ymd')}_{$toDate->format('Ymd')}_{$request->group}";

        // Obtener datos del caché o generarlos si no existen
        return Cache::tags(["merchant_{$merchantId}"])->remember($cacheKey, now()->addMinutes(15), function () use ($merchantId, $fromDate, $toDate, $request) {
            // Format para agrupar por día u hora
            $groupFormat = $request->group === 'day' ? 'Y-m-d' : 'Y-m-d H:00';
            
            // Field para SQL, dependiendo de la agrupación
            $groupField = $request->group === 'day' 
                ? DB::raw('DATE(issued_at) as label') 
                : DB::raw('CONCAT(DATE(issued_at), " ", HOUR(issued_at), ":00") as label');

            // Obtener datos agrupados por día u hora
            $salesData = Invoice::where('merchant_id', $merchantId)
                ->whereBetween('issued_at', [$fromDate, $toDate])
                ->select(
                    $groupField,
                    DB::raw('SUM(total) as value'),
                    DB::raw('COUNT(*) as invoice_count'),
                    DB::raw('SUM(tax) as tax_sum')
                )
                ->groupBy(DB::raw($request->group === 'day' ? 'DATE(issued_at)' : 'DATE(issued_at), HOUR(issued_at)'))
                ->orderBy(DB::raw($request->group === 'day' ? 'DATE(issued_at)' : 'DATE(issued_at), HOUR(issued_at)'))
                ->get();

            // Calcular totales
            $totalSales = $salesData->sum('value');
            $totalInvoices = $salesData->sum('invoice_count');
            $totalIVA = $salesData->sum('tax_sum');

            // Formatear datos para el gráfico
            $graph = $salesData->map(function ($item) {
                return [
                    'label' => $item->label,
                    'value' => (float) $item->value
                ];
            });

            // Retornar respuesta JSON
            return response()->json([
                'total_sales' => (float) $totalSales,
                'total_invoices' => (int) $totalInvoices,
                'total_iva' => (float) $totalIVA,
                'graph' => $graph
            ]);
        });
    }
} 