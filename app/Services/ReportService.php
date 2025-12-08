<?php

namespace App\Services;

use App\Models\Project;
use App\Models\StockMovement;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\InventoryItem;
use App\Models\MaterialIssuance;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportService
{
    public function getInventoryMovementReport(array $filters = [])
    {
        $query = StockMovement::with(['inventoryItem', 'createdBy']);

        if (isset($filters['item_id'])) {
            $query->where('inventory_item_id', $filters['item_id']);
        }

        if (isset($filters['movement_type'])) {
            $query->where('movement_type', $filters['movement_type']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function getPurchaseHistoryReport(array $filters = [])
    {
        $query = PurchaseOrder::with(['supplier', 'items.inventoryItem']);

        if (isset($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('po_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('po_date', '<=', $filters['date_to']);
        }

        return $query->orderBy('po_date', 'desc')->get();
    }

    public function getProjectConsumptionReport(int $projectId, array $filters = [])
    {
        $query = MaterialIssuance::with(['items.inventoryItem'])
            ->where('project_id', $projectId)
            ->where('status', 'issued');

        if (isset($filters['date_from'])) {
            $query->whereDate('issuance_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('issuance_date', '<=', $filters['date_to']);
        }

        return $query->orderBy('issuance_date', 'desc')->get();
    }

    public function getSupplierPerformanceReport(array $filters = [])
    {
        $query = Supplier::with(['purchaseOrders' => function ($q) use ($filters) {
            if (isset($filters['date_from'])) {
                $q->whereDate('po_date', '>=', $filters['date_from']);
            }
            if (isset($filters['date_to'])) {
                $q->whereDate('po_date', '<=', $filters['date_to']);
            }
        }]);

        $suppliers = $query->get();

        return $suppliers->map(function ($supplier) {
            $orders = $supplier->purchaseOrders;
            $totalOrders = $orders->count();
            $totalAmount = $orders->sum('total_amount');
            $completedOrders = $orders->where('status', 'completed')->count();
            $onTimeDeliveries = $orders->filter(function ($po) {
                if (!$po->expected_delivery_date) return false;
                $lastGR = $po->goodsReceipts()->latest()->first();
                if (!$lastGR) return false;
                return $lastGR->gr_date <= $po->expected_delivery_date;
            })->count();

            return [
                'supplier' => $supplier,
                'total_orders' => $totalOrders,
                'total_amount' => $totalAmount,
                'completed_orders' => $completedOrders,
                'on_time_deliveries' => $onTimeDeliveries,
                'on_time_rate' => $totalOrders > 0 ? ($onTimeDeliveries / $totalOrders) * 100 : 0,
            ];
        });
    }

    public function getDelayedProjectsReport()
    {
        $today = Carbon::today();

        return Project::where('status', 'active')
            ->where('end_date', '<', $today)
            ->whereNull('actual_end_date')
            ->with(['projectManager', 'client'])
            ->orderBy('end_date', 'asc')
            ->get()
            ->map(function ($project) use ($today) {
                $daysDelayed = $today->diffInDays($project->end_date);
                return [
                    'project' => $project,
                    'days_delayed' => $daysDelayed,
                ];
            });
    }

    public function exportToCsv($data, $reportName, $isExcel = false)
    {
        $extension = $isExcel ? 'xlsx' : 'csv';
        $contentType = $isExcel ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' : 'text/csv; charset=UTF-8';
        $filename = "{$reportName}_" . date('Y-m-d') . ".{$extension}";
        $headers = [
            'Content-Type' => $contentType,
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($data, $reportName, $isExcel) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8 (Excel compatibility)
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            if ($data->isEmpty()) {
                fputcsv($file, ['No data available']);
                fclose($file);
                return;
            }
            
            // Format data based on report type
            $formattedData = $this->formatDataForCsv($data, $reportName);
            
            if (count($formattedData) > 0) {
                // Write headers
                fputcsv($file, array_keys($formattedData[0]));
                
                // Write data
                foreach ($formattedData as $row) {
                    fputcsv($file, $row);
                }
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
    
    protected function formatDataForCsv($data, $reportName)
    {
        $formatted = [];
        
        switch ($reportName) {
            case 'inventory_movement':
                foreach ($data as $movement) {
                    $reference = '';
                    if ($movement->reference_type && $movement->reference_id) {
                        $refParts = explode('\\', $movement->reference_type);
                        $refName = end($refParts);
                        $reference = str_replace('App\\Models\\', '', $refName) . ' #' . $movement->reference_id;
                    }
                    if ($movement->notes) {
                        $reference .= ($reference ? ' - ' : '') . $movement->notes;
                    }
                    
                    $formatted[] = [
                        'Date' => $movement->created_at->format('Y-m-d'),
                        'Time' => $movement->created_at->format('H:i:s'),
                        'Item Code' => $movement->inventoryItem->item_code ?? '',
                        'Item Name' => $movement->inventoryItem->name,
                        'Movement Type' => ucfirst(str_replace('_', ' ', $movement->movement_type)),
                        'Quantity' => $movement->quantity,
                        'Unit' => $movement->inventoryItem->unit_of_measure ?? '',
                        'Balance After' => $movement->balance_after,
                        'Reference' => $reference ?: 'N/A',
                        'Created By' => $movement->createdBy->name ?? 'System',
                    ];
                }
                break;
                
            case 'purchase_history':
                foreach ($data as $po) {
                    $formatted[] = [
                        'PO Number' => $po->po_number,
                        'Date' => $po->po_date ? $po->po_date->format('Y-m-d') : '',
                        'Supplier' => $po->supplier->name ?? '',
                        'Status' => ucfirst($po->status),
                        'Total Amount' => number_format($po->total_amount ?? 0, 2),
                        'Items Count' => $po->items->count(),
                    ];
                }
                break;
                
            case 'supplier_performance':
                foreach ($data as $item) {
                    $formatted[] = [
                        'Supplier Name' => $item['supplier']->name,
                        'Total Orders' => $item['total_orders'],
                        'Completed Orders' => $item['completed_orders'],
                        'Total Amount' => number_format($item['total_amount'], 2),
                        'On-Time Deliveries' => $item['on_time_deliveries'],
                        'On-Time Rate (%)' => number_format($item['on_time_rate'], 2),
                    ];
                }
                break;
                
            case 'project_consumption':
                foreach ($data as $issuance) {
                    foreach ($issuance->items as $item) {
                        $formatted[] = [
                            'Issuance Date' => $issuance->issuance_date ? $issuance->issuance_date->format('Y-m-d') : '',
                            'MI Number' => $issuance->issuance_number ?? '',
                            'Item Code' => $item->inventoryItem->item_code ?? '',
                            'Item Name' => $item->inventoryItem->name,
                            'Quantity' => number_format($item->quantity, 2),
                            'Unit' => $item->inventoryItem->unit_of_measure ?? '',
                        ];
                    }
                }
                break;
                
            default:
                // Generic format
                foreach ($data as $row) {
                    if (is_array($row) || is_object($row)) {
                        $formatted[] = (array) $row;
                    }
                }
        }
        
        return $formatted;
    }
}

