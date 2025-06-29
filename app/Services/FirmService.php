<?php

namespace App\Services;

use App\Models\Firm;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class FirmService
{
  /**
   * Get paginated firms with filters and search
   */
  public function getPaginatedFirms(Request $request): LengthAwarePaginator
  {
    $query = Firm::query()
      ->with(['address', 'tags'])
      ->withCount(['contacts', 'projects'])
      ->selectRaw('firms.*,
                (SELECT COUNT(*) FROM projects
                 INNER JOIN contacts ON projects.contact_id = contacts.uuid
                 WHERE contacts.firm_id = firms.uuid AND projects.status = "active") as active_projects_count')
      ->selectRaw('(SELECT MAX(interaction_date) FROM interactions
                         INNER JOIN contacts ON interactions.contact_id = contacts.uuid
                         WHERE contacts.firm_id = firms.uuid) as last_interaction_date');

    // Apply search
    if ($request->filled('search')) {
      $search = $request->get('search');
      $query->where(function (Builder $q) use ($search) {
        $q->where('name', 'like', "%{$search}%")
          ->orWhere('slogan', 'like', "%{$search}%")
          ->orWhere('url', 'like', "%{$search}%")
          ->orWhereHas('address', function (Builder $addressQuery) use ($search) {
            $addressQuery->where('city', 'like', "%{$search}%")
              ->orWhere('state', 'like', "%{$search}%")
              ->orWhere('country', 'like', "%{$search}%");
          })
          ->orWhereHas('tags', function (Builder $tagQuery) use ($search) {
            $tagQuery->where('name', 'like', "%{$search}%");
          });
      });
    }

    // Apply status filter
    if ($request->filled('status') && $request->get('status') !== 'all') {
      $query->whereHas('tags', function (Builder $tagQuery) use ($request) {
        $tagQuery->where('name', $request->get('status'));
      });
    }

    // Apply industry filter
    if ($request->filled('industry') && $request->get('industry') !== 'all') {
      $query->whereHas('tags', function (Builder $tagQuery) use ($request) {
        $tagQuery->where('name', $request->get('industry'));
      });
    }

    // Apply size filter
    if ($request->filled('size') && $request->get('size') !== 'all') {
      $query->whereHas('tags', function (Builder $tagQuery) use ($request) {
        $tagQuery->where('name', $request->get('size'));
      });
    }

    // Apply sorting
    $sortField = $request->get('sort', 'name');
    $sortDirection = $request->get('direction', 'asc');

    switch ($sortField) {
      case 'contacts_count':
        $query->orderBy('contacts_count', $sortDirection);
        break;
      case 'projects_count':
        $query->orderBy('projects_count', $sortDirection);
        break;
      case 'created_at':
        $query->orderBy('created_at', $sortDirection);
        break;
      case 'last_interaction':
        $query->orderBy('last_interaction_date', $sortDirection);
        break;
      default:
        $query->orderBy($sortField, $sortDirection);
    }

    $perPage = $request->get('per_page', 15);

    return $query->paginate($perPage)->withQueryString();
  }

  /**
   * Get firm statistics
   */
  public function getFirmStats(): array
  {
    $total = Firm::count();

    $statusCounts = Firm::whereHas('tags', function (Builder $query) {
      $query->whereIn('name', ['active', 'inactive', 'prospect']);
    })->with('tags')->get()->groupBy(function ($firm) {
      $statusTag = $firm->tags->whereIn('name', ['active', 'inactive', 'prospect'])->first();
      return $statusTag ? $statusTag->name : 'unknown';
    });

    $active = $statusCounts->get('active', collect())->count();
    $inactive = $statusCounts->get('inactive', collect())->count();
    $prospects = $statusCounts->get('prospect', collect())->count();

    $totalContacts = DB::table('contacts')->count();
    $totalProjects = DB::table('projects')
      ->join('contacts', 'projects.contact_id', '=', 'contacts.uuid')
      ->count();

    return [
      'total' => $total,
      'active' => $active,
      'inactive' => $inactive,
      'prospects' => $prospects,
      'total_contacts' => $totalContacts,
      'total_projects' => $totalProjects,
    ];
  }

  /**
   * Create a new firm
   */
  public function createFirm(array $data): Firm
  {
    return DB::transaction(function () use ($data) {
      $firm = Firm::create([
        'name' => $data['name'],
        'slogan' => $data['slogan'] ?? null,
        'url' => $data['url'] ?? null,
      ]);

      // Create address if provided
      if (!empty($data['address'])) {
        $firm->address()->create([
          'type' => 'primary',
          'street' => $data['address']['street'] ?? null,
          'city' => $data['address']['city'] ?? null,
          'state' => $data['address']['state'] ?? null,
          'country' => $data['address']['country'] ?? null,
        ]);
      }

      // Attach tags if provided
      if (!empty($data['tags'])) {
        $firm->attachTags($data['tags']);
      }

      // Set status tag
      if (!empty($data['status'])) {
        $firm->attachTag($data['status']);
      }

      // Set industry tag
      if (!empty($data['industry'])) {
        $firm->attachTag($data['industry']);
      }

      // Set size tag
      if (!empty($data['size'])) {
        $firm->attachTag($data['size']);
      }

      return $firm->load(['address', 'tags']);
    });
  }

  /**
   * Update an existing firm
   */
  public function updateFirm(Firm $firm, array $data): Firm
  {
    return DB::transaction(function () use ($firm, $data) {
      $firm->update([
        'name' => $data['name'] ?? $firm->name,
        'slogan' => $data['slogan'] ?? $firm->slogan,
        'url' => $data['url'] ?? $firm->url,
      ]);

      // Update address
      if (!empty($data['address'])) {
        $firm->address()->updateOrCreate(
          ['addressable_id' => $firm->uuid, 'addressable_type' => Firm::class],
          [
            'type' => 'primary',
            'street' => $data['address']['street'] ?? null,
            'city' => $data['address']['city'] ?? null,
            'state' => $data['address']['state'] ?? null,
            'country' => $data['address']['country'] ?? null,
          ]
        );
      }

      // Update tags
      if (isset($data['tags'])) {
        $firm->syncTags($data['tags']);
      }

      // Update status
      if (!empty($data['status'])) {
        $firm->detachTags(['active', 'inactive', 'prospect']);
        $firm->attachTag($data['status']);
      }

      // Update industry
      if (isset($data['industry'])) {
        // Remove existing industry tags (you might want to maintain a list)
        $firm->attachTag($data['industry']);
      }

      // Update size
      if (isset($data['size'])) {
        // Remove existing size tags (you might want to maintain a list)
        $firm->attachTag($data['size']);
      }

      return $firm->load(['address', 'tags']);
    });
  }

  /**
   * Delete a firm
   */
  public function deleteFirm(Firm $firm): bool
  {
    return DB::transaction(function () use ($firm) {
      // The model's boot method handles cascading deletes
      return $firm->delete();
    });
  }

  /**
   * Bulk delete firms
   */
  public function bulkDeleteFirms(array $firmUuids): int
  {
    return DB::transaction(function () use ($firmUuids) {
      $firms = Firm::whereIn('uuid', $firmUuids)->get();

      $deletedCount = 0;
      foreach ($firms as $firm) {
        if ($this->deleteFirm($firm)) {
          $deletedCount++;
        }
      }

      return $deletedCount;
    });
  }

  /**
   * Get a firm by UUID with relationships
   */
  public function getFirmByUuid(string $uuid): ?Firm
  {
    return Firm::with([
      'address',
      'tags',
      'contacts' => function ($query) {
        $query->with(['emails', 'phones'])->latest();
      },
      'projects' => function ($query) {
        $query->with(['contact'])->latest();
      }
    ])
      ->withCount(['contacts', 'projects'])
      ->where('uuid', $uuid)
      ->first();
  }

  /**
   * Get available industries from tags
   */
  public function getIndustries(): Collection
  {
    return DB::table('taggables')
      ->join('tags', 'taggables.tag_id', '=', 'tags.id')
      ->where('taggables.taggable_type', Firm::class)
      ->whereNotIn('tags.name', ['active', 'inactive', 'prospect', 'small', 'medium', 'large', 'enterprise'])
      ->distinct()
      ->pluck('tags.name')
      ->sort()
      ->values();
  }

  /**
   * Get available sizes
   */
  public function getSizes(): array
  {
    return ['small', 'medium', 'large', 'enterprise'];
  }

  /**
   * Export firms data
   */
  public function exportFirms(Request $request): Collection
  {
    $query = Firm::query()
      ->with(['address', 'tags'])
      ->withCount(['contacts', 'projects']);

    // Apply same filters as pagination
    if ($request->filled('search')) {
      $search = $request->get('search');
      $query->where(function (Builder $q) use ($search) {
        $q->where('name', 'like', "%{$search}%")
          ->orWhere('slogan', 'like', "%{$search}%")
          ->orWhere('url', 'like', "%{$search}%");
      });
    }

    if ($request->filled('status') && $request->get('status') !== 'all') {
      $query->whereHas('tags', function (Builder $tagQuery) use ($request) {
        $tagQuery->where('name', $request->get('status'));
      });
    }

    // If specific firms are selected
    if ($request->filled('selected')) {
      $query->whereIn('uuid', $request->get('selected'));
    }

    return $query->get()->map(function ($firm) {
      $statusTag = $firm->tags->whereIn('name', ['active', 'inactive', 'prospect'])->first();
      $industryTags = $firm->tags->whereNotIn('name', ['active', 'inactive', 'prospect', 'small', 'medium', 'large', 'enterprise']);
      $sizeTag = $firm->tags->whereIn('name', ['small', 'medium', 'large', 'enterprise'])->first();

      return [
        'name' => $firm->name,
        'slogan' => $firm->slogan,
        'website' => $firm->url,
        'status' => $statusTag?->name,
        'industry' => $industryTags->pluck('name')->join(', '),
        'size' => $sizeTag?->name,
        'address' => $firm->address ? [
          $firm->address->street,
          $firm->address->city,
          $firm->address->state,
          $firm->address->country
        ] : null,
        'contacts_count' => $firm->contacts_count,
        'projects_count' => $firm->projects_count,
        'created_at' => $firm->created_at->format('Y-m-d H:i:s'),
      ];
    });
  }

  /**
   * Search firms for autocomplete/select components
   */
  public function searchFirms(string $query, int $limit = 10): Collection
  {
    return Firm::where('name', 'like', "%{$query}%")
      ->orWhere('slogan', 'like', "%{$query}%")
      ->limit($limit)
      ->get(['uuid', 'name', 'slogan']);
  }
}
