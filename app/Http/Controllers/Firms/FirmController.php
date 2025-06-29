<?php

namespace App\Http\Controllers\Firms;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFirmRequest;
use App\Http\Requests\UpdateFirmRequest;
use App\Models\Firm;
use App\Services\FirmService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FirmController extends Controller
{
  public function __construct(
    private FirmService $firmService
  ) {}

  /**
   * Display a listing of firms
   */
  public function index(Request $request): InertiaResponse
  {
    $firms = $this->firmService->getPaginatedFirms($request);
    $stats = $this->firmService->getFirmStats();
    $industries = $this->firmService->getIndustries();
    $sizes = $this->firmService->getSizes();

    return Inertia::render('firms/Index', [
      'firms' => $firms,
      'stats' => $stats,
      'industries' => $industries,
      'sizes' => $sizes,
      'filters' => [
        'search' => $request->get('search'),
        'status' => $request->get('status'),
        'industry' => $request->get('industry'),
        'size' => $request->get('size'),
        'sort' => $request->get('sort', 'name'),
        'direction' => $request->get('direction', 'asc'),
        'per_page' => $request->get('per_page', 15),
      ],
    ]);
  }

  /**
   * Show the form for creating a new firm
   */
  public function create(): InertiaResponse
  {
    $industries = $this->firmService->getIndustries();
    $sizes = $this->firmService->getSizes();

    return Inertia::render('firms/Create', [
      'industries' => $industries,
      'sizes' => $sizes,
    ]);
  }

  /**
   * Store a newly created firm
   */
  public function store(StoreFirmRequest $request): RedirectResponse
  {
    try {
      $firm = $this->firmService->createFirm($request->validated());

      return redirect()
        ->route('firms.show', $firm->uuid)
        ->with('success', 'Firm created successfully.');
    } catch (\Exception $e) {
      return redirect()
        ->back()
        ->withInput()
        ->with('error', 'Failed to create firm. Please try again.');
    }
  }

  /**
   * Display the specified firm
   */
  public function show(string $uuid): InertiaResponse
  {
    $firm = $this->firmService->getFirmByUuid($uuid);

    if (!$firm) {
      abort(404, 'Firm not found');
    }

    // Get additional data for the firm detail view
    $recentInteractions = $firm->contacts()
      ->with(['interactions' => function ($query) {
        $query->latest()->limit(5);
      }])
      ->get()
      ->pluck('interactions')
      ->flatten()
      ->sortByDesc('interaction_date')
      ->take(10);

    $activeProjects = $firm->projects()
      ->where('status', 'active')
      ->with(['contact', 'tasks'])
      ->latest()
      ->get();

    return Inertia::render('firms/Show', [
      'firm' => $firm,
      'recentInteractions' => $recentInteractions,
      'activeProjects' => $activeProjects,
    ]);
  }

  /**
   * Show the form for editing the specified firm
   */
  public function edit(string $uuid): InertiaResponse
  {
    $firm = $this->firmService->getFirmByUuid($uuid);

    if (!$firm) {
      abort(404, 'Firm not found');
    }

    $industries = $this->firmService->getIndustries();
    $sizes = $this->firmService->getSizes();

    return Inertia::render('firms/Edit', [
      'firm' => $firm,
      'industries' => $industries,
      'sizes' => $sizes,
    ]);
  }

  /**
   * Update the specified firm
   */
  public function update(UpdateFirmRequest $request, string $uuid): RedirectResponse
  {
    $firm = Firm::where('uuid', $uuid)->firstOrFail();

    try {
      $updatedFirm = $this->firmService->updateFirm($firm, $request->validated());

      return redirect()
        ->route('firms.show', $updatedFirm->uuid)
        ->with('success', 'Firm updated successfully.');
    } catch (\Exception $e) {
      return redirect()
        ->back()
        ->withInput()
        ->with('error', 'Failed to update firm. Please try again.');
    }
  }

  /**
   * Remove the specified firm
   */
  public function destroy(string $uuid): RedirectResponse
  {
    $firm = Firm::where('uuid', $uuid)->firstOrFail();

    try {
      $this->firmService->deleteFirm($firm);

      return redirect()
        ->route('firms.index')
        ->with('success', 'Firm deleted successfully.');
    } catch (\Exception $e) {
      return redirect()
        ->back()
        ->with('error', 'Failed to delete firm. Please try again.');
    }
  }

  /**
   * Bulk delete selected firms
   */
  public function bulkDelete(Request $request): RedirectResponse
  {
    $request->validate([
      'firm_uuids' => 'required|array|min:1',
      'firm_uuids.*' => 'required|string|exists:firms,uuid',
    ]);

    try {
      $deletedCount = $this->firmService->bulkDeleteFirms($request->get('firm_uuids'));

      return redirect()
        ->route('firms.index')
        ->with('success', "Successfully deleted {$deletedCount} firms.");
    } catch (\Exception $e) {
      return redirect()
        ->back()
        ->with('error', 'Failed to delete selected firms. Please try again.');
    }
  }

  /**
   * Export firms data
   */
  public function export(Request $request): BinaryFileResponse
  {
    try {
      $firms = $this->firmService->exportFirms($request);

      $filename = 'firms_export_' . now()->format('Y-m-d_H-i-s') . '.csv';
      $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => "attachment; filename=\"{$filename}\"",
      ];

      $callback = function () use ($firms) {
        $file = fopen('php://output', 'w');

        // Add CSV headers
        fputcsv($file, [
          'Name',
          'Slogan',
          'Website',
          'Status',
          'Industry',
          'Size',
          'Street',
          'City',
          'State',
          'Country',
          'Contacts Count',
          'Projects Count',
          'Created At',
        ]);

        // Add data rows
        foreach ($firms as $firm) {
          $address = $firm['address'] ?? [];
          fputcsv($file, [
            $firm['name'],
            $firm['slogan'],
            $firm['website'],
            $firm['status'],
            $firm['industry'],
            $firm['size'],
            $address[0] ?? '', // street
            $address[1] ?? '', // city
            $address[2] ?? '', // state
            $address[3] ?? '', // country
            $firm['contacts_count'],
            $firm['projects_count'],
            $firm['created_at'],
          ]);
        }

        fclose($file);
      };

      return response()->stream($callback, 200, $headers);
    } catch (\Exception $e) {
      return redirect()
        ->back()
        ->with('error', 'Failed to export firms. Please try again.');
    }
  }

  /**
   * Search firms for autocomplete/select components
   */
  public function search(Request $request): Response
  {
    $request->validate([
      'q' => 'required|string|min:1|max:100',
      'limit' => 'sometimes|integer|min:1|max:50',
    ]);

    $query = $request->get('q');
    $limit = $request->get('limit', 10);

    $firms = $this->firmService->searchFirms($query, $limit);

    return response()->json([
      'data' => $firms->map(function ($firm) {
        return [
          'uuid' => $firm->uuid,
          'name' => $firm->name,
          'slogan' => $firm->slogan,
          'label' => $firm->name . ($firm->slogan ? " - {$firm->slogan}" : ''),
        ];
      }),
    ]);
  }

  /**
   * Get firm statistics for dashboard/reports
   */
  public function stats(): Response
  {
    $stats = $this->firmService->getFirmStats();

    return response()->json([
      'data' => $stats,
    ]);
  }

  /**
   * Restore a soft-deleted firm (if using soft deletes)
   */
  public function restore(string $uuid): RedirectResponse
  {
    // This would be implemented if using soft deletes
    // For now, return a not implemented response
    return redirect()
      ->back()
      ->with('error', 'Restore functionality not implemented.');
  }

  /**
   * Force delete a firm (if using soft deletes)
   */
  public function forceDelete(string $uuid): RedirectResponse
  {
    // This would be implemented if using soft deletes
    // For now, return a not implemented response
    return redirect()
      ->back()
      ->with('error', 'Force delete functionality not implemented.');
  }
}
