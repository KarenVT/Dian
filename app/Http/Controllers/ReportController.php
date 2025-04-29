<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    /**
     * Obtiene los datos del reporte de ventas.
     *
     * @param int $companyId ID del comercio
     * @param string $from Fecha inicial
     * @param string $to Fecha final
     * @param string $group Tipo de agrupación (day/hour)
     * @return array
     */
    public function getReportData($companyId, $from, $to, $group)
    {
        // Convertir fechas a objetos Carbon
        $fromDate = $from === 'today' 
            ? Carbon::today()->startOfDay() 
            : Carbon::parse($from)->startOfDay();
            
        $toDate = $to === 'today' 
            ? Carbon::today()->endOfDay() 
            : Carbon::parse($to)->endOfDay();

        // Clave única para el caché
        $cacheKey = "sales_report_{$companyId}_{$fromDate->format('Ymd')}_{$toDate->format('Ymd')}_{$group}";

        // Obtener datos del caché o generarlos si no existen
        return Cache::tags(["company_{$companyId}"])->remember($cacheKey, now()->addMinutes(15), function () use ($companyId, $fromDate, $toDate, $group) {
            // Format para agrupar por día u hora
            $groupFormat = $group === 'day' ? 'Y-m-d' : 'Y-m-d H:00';
            
            // Field para SQL, dependiendo de la agrupación
            $groupField = $group === 'day' 
                ? DB::raw('DATE(issued_at) as label') 
                : DB::raw('CONCAT(DATE(issued_at), " ", HOUR(issued_at), ":00") as label');

            // Obtener datos agrupados por día u hora
            $salesData = Invoice::where('company_id', $companyId)
                ->whereBetween('issued_at', [$fromDate, $toDate])
                ->select(
                    $groupField,
                    DB::raw('SUM(total) as value'),
                    DB::raw('COUNT(*) as invoice_count'),
                    DB::raw('SUM(tax) as tax_sum')
                )
                ->groupBy(DB::raw($group === 'day' ? 'DATE(issued_at)' : 'DATE(issued_at), HOUR(issued_at)'))
                ->orderBy(DB::raw($group === 'day' ? 'DATE(issued_at)' : 'DATE(issued_at), HOUR(issued_at)'))
                ->get();

            // Calcular totales
            $totalSales = $salesData->sum('value');
            $invoiceCount = $salesData->sum('invoice_count');
            $totalTax = $salesData->sum('tax_sum');

            // Obtener datos para ventas por hora
            $salesByHour = [];
            if ($group === 'hour' || $fromDate->isSameDay($toDate)) {
                $salesByHour = Invoice::where('company_id', $companyId)
                    ->whereBetween('issued_at', [$fromDate, $toDate])
                    ->select(
                        DB::raw('HOUR(issued_at) as hour'),
                        DB::raw('SUM(total) as total')
                    )
                    ->groupBy(DB::raw('HOUR(issued_at)'))
                    ->orderBy(DB::raw('HOUR(issued_at)'))
                    ->get()
                    ->map(function ($item) {
                        return [
                            'hour' => $item->hour . ':00',
                            'total' => (float) $item->total
                        ];
                    });
            }

            // Obtener conteo de documentos pendientes y rechazados por DIAN
            $pendingDian = Invoice::where('company_id', $companyId)
                ->whereBetween('issued_at', [$fromDate, $toDate])
                ->whereHas('dianResolution', function ($query) {
                    $query->where('dian_status', 'PENDING');
                })
                ->count();

            $rejectedDian = Invoice::where('company_id', $companyId)
                ->whereBetween('issued_at', [$fromDate, $toDate])
                ->whereHas('dianResolution', function ($query) {
                    $query->where('dian_status', 'REJECTED');
                })
                ->count();

            // Formatear datos para el gráfico
            $graph = $salesData->map(function ($item) {
                return [
                    'label' => $item->label,
                    'value' => (float) $item->value
                ];
            });

            return [
                'total_sales' => (float) $totalSales,
                'invoice_count' => (int) $invoiceCount,
                'total_tax' => (float) $totalTax,
                'pending_dian' => (int) $pendingDian,
                'rejected_dian' => (int) $rejectedDian,
                'sales_by_hour' => $salesByHour,
                'graph' => $graph
            ];
        });
    }

    /**
     * Exporta el reporte de ventas a un archivo CSV.
     *
     * @param int $companyId ID del comercio
     * @param string $from Fecha inicial
     * @param string $to Fecha final
     * @param string $group Tipo de agrupación (day/hour)
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportReport($companyId, $from, $to, $group)
    {
        // Convertir fechas a objetos Carbon
        $fromDate = $from === 'today' 
            ? Carbon::today()->startOfDay() 
            : Carbon::parse($from)->startOfDay();
            
        $toDate = $to === 'today' 
            ? Carbon::today()->endOfDay() 
            : Carbon::parse($to)->endOfDay();

        // Clave única para el archivo CSV
        $filename = "reporte_ventas_{$companyId}_{$fromDate->format('Ymd')}_{$toDate->format('Ymd')}.csv";
        $tempFile = storage_path('app/temp/' . $filename);
        
        // Asegurarse de que exista el directorio
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }
        
        // Abrir el archivo para escritura
        $file = fopen($tempFile, 'w');
        
        // Escribir la cabecera del CSV
        fputcsv($file, [
            'Fecha/Hora',
            'Total Ventas',
            'Cantidad Facturas',
            'Total IVA'
        ]);
        
        // Field para SQL, dependiendo de la agrupación
        $groupField = $group === 'day' 
            ? DB::raw('DATE(issued_at) as label') 
            : DB::raw('CONCAT(DATE(issued_at), " ", HOUR(issued_at), ":00") as label');

        // Obtener datos agrupados por día u hora
        $salesData = Invoice::where('company_id', $companyId)
            ->whereBetween('issued_at', [$fromDate, $toDate])
            ->select(
                $groupField,
                DB::raw('SUM(total) as value'),
                DB::raw('COUNT(*) as invoice_count'),
                DB::raw('SUM(tax) as tax_sum')
            )
            ->groupBy(DB::raw($group === 'day' ? 'DATE(issued_at)' : 'DATE(issued_at), HOUR(issued_at)'))
            ->orderBy(DB::raw($group === 'day' ? 'DATE(issued_at)' : 'DATE(issued_at), HOUR(issued_at)'))
            ->get();
            
        // Escribir los datos en el CSV
        foreach ($salesData as $row) {
            fputcsv($file, [
                $row->label,
                $row->value,
                $row->invoice_count,
                $row->tax_sum
            ]);
        }
        
        // Escribir fila de totales
        fputcsv($file, [
            'TOTAL',
            $salesData->sum('value'),
            $salesData->sum('invoice_count'),
            $salesData->sum('tax_sum')
        ]);
        
        // Cerrar el archivo
        fclose($file);
        
        // Devolver el archivo para descarga
        return response()->download($tempFile, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ])->deleteFileAfterSend(true);
    }
} 