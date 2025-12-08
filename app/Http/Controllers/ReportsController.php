<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportsController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index()
    {
        return view('reports.index');
    }

    public function inventoryMovement(Request $request)
    {
        $filters = $request->only(['item_id', 'movement_type', 'date_from', 'date_to']);
        $data = $this->reportService->getInventoryMovementReport($filters);

        if ($request->has('export')) {
            return $this->exportReport($data, 'inventory_movement', $request->export);
        }

        return view('reports.inventory_movement', compact('data', 'filters'));
    }

    public function purchaseHistory(Request $request)
    {
        $filters = $request->only(['supplier_id', 'status', 'date_from', 'date_to']);
        $data = $this->reportService->getPurchaseHistoryReport($filters);

        if ($request->has('export')) {
            return $this->exportReport($data, 'purchase_history', $request->export);
        }

        return view('reports.purchase_history', compact('data', 'filters'));
    }

    public function projectConsumption(Request $request)
    {
        $filters = $request->only(['project_id', 'date_from', 'date_to']);
        $data = collect();
        
        if ($request->has('project_id') && $request->project_id) {
            $data = $this->reportService->getProjectConsumptionReport($request->project_id, $filters);
        }

        if ($request->has('export') && $data->isNotEmpty()) {
            return $this->exportReport($data, 'project_consumption', $request->export);
        }

        $projects = \App\Models\Project::orderBy('name')->get();
        return view('reports.project_consumption', compact('data', 'filters', 'projects'));
    }

    public function supplierPerformance(Request $request)
    {
        $filters = $request->only(['date_from', 'date_to']);
        $data = $this->reportService->getSupplierPerformanceReport($filters);

        if ($request->has('export')) {
            return $this->exportReport($data, 'supplier_performance', $request->export);
        }

        return view('reports.supplier_performance', compact('data', 'filters'));
    }

    public function delayedProjects(Request $request)
    {
        $data = $this->reportService->getDelayedProjectsReport();

        if ($request->has('export')) {
            return $this->exportReport($data, 'delayed_projects', $request->export);
        }

        return view('reports.delayed_projects', compact('data'));
    }

    protected function exportReport($data, $reportName, $format)
    {
        $printedBy = auth()->user();
        $filters = request()->except(['export']);
        
        switch ($format) {
            case 'pdf':
                try {
                    // Try to load dedicated PDF view
                    $pdfView = "reports.pdf.{$reportName}";
                    if (view()->exists($pdfView)) {
                        $pdf = Pdf::loadView($pdfView, compact('data', 'printedBy', 'filters'));
                    } else {
                        // Fallback to regular view
                        $view = "reports.{$reportName}";
                        if (!view()->exists($view)) {
                            return redirect()->back()->with('error', 'PDF export template not found.');
                        }
                        $pdf = Pdf::loadView($view, compact('data', 'printedBy', 'filters'));
                    }
                    return $pdf->download("{$reportName}_" . date('Y-m-d') . ".pdf");
                } catch (\Exception $e) {
                    \Log::error('PDF Export Error: ' . $e->getMessage());
                    return redirect()->back()->with('error', 'Error generating PDF: ' . $e->getMessage());
                }

            case 'csv':
            case 'excel':
                return $this->reportService->exportToCsv($data, $reportName, $format === 'excel');

            case 'json':
                return response()->json($data);

            default:
                return redirect()->back()->with('error', 'Invalid export format.');
        }
    }
}

