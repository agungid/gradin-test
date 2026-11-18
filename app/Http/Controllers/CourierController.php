<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourierRequest;
use App\Http\Requests\UpdateCourierRequest;
use App\Models\Courier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourierController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Courier::query();

        $this->applySearchFilter($query, $request->query('search'));
        $this->applyLevelFilter($query, $request->query('level'));
        $this->applySorting($query, $request);

        $perPage = max(1, min((int) $request->query('per_page', 15), 100));

        return response()->json($query->paginate($perPage));
    }

    public function store(StoreCourierRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['status'] = $data['status'] ?? 'active';
        $data['registered_at'] = $data['registered_at'] ?? now();

        $courier = Courier::create($data);

        return response()->json([
            'data' => $courier,
        ], 201);
    }

    public function show(Courier $courier): JsonResponse
    {
        return response()->json([
            'data' => $courier,
        ]);
    }

    public function update(UpdateCourierRequest $request, Courier $courier): JsonResponse
    {
        $courier->update($request->validated());

        return response()->json([
            'data' => $courier->fresh(),
        ]);
    }

    public function destroy(Courier $courier): JsonResponse
    {
        $courier->delete();

        return response()->json([
            'message' => 'Courier deleted successfully.',
        ]);
    }

    private function applySearchFilter(Builder $query, mixed $search): void
    {
        if (! is_string($search) || trim($search) === '') {
            return;
        }

        $terms = preg_split('/\s+/', trim($search)) ?: [];

        foreach ($terms as $term) {
            if ($term === '') {
                continue;
            }

            $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], mb_strtolower($term));
            $query->whereRaw('LOWER(name) LIKE ?', ["%{$escaped}%"]);
        }
    }

    private function applyLevelFilter(Builder $query, mixed $levelParam): void
    {
        if ($levelParam === null || $levelParam === '') {
            return;
        }

        $rawLevels = is_array($levelParam)
            ? $levelParam
            : explode(',', (string) $levelParam);

        $levels = array_values(array_unique(array_filter(
            array_map(static fn (mixed $val): int => (int) trim((string) $val), $rawLevels),
            static fn (int $lvl): bool => $lvl >= Courier::MIN_LEVEL && $lvl <= Courier::MAX_LEVEL,
        )));

        if ($levels !== []) {
            $query->whereIn('level', $levels);
        }
    }

    private function applySorting(Builder $query, Request $request): void
    {
        $sortBy = $request->query('sort_by', $request->query('sort', 'name'));
        $allowedSorts = ['name', 'registered_at'];

        if (! in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'name';
        }

        $defaultDir = $sortBy === 'registered_at' ? 'desc' : 'asc';
        $sortDir = strtolower((string) $request->query('sort_dir', $defaultDir));

        if (! in_array($sortDir, ['asc', 'desc'], true)) {
            $sortDir = $defaultDir;
        }

        $query->orderBy($sortBy, $sortDir);
    }
}
