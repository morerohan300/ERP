<?php

declare(strict_types=1);

namespace Modules\Core\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    /** @return array{0: array<string, mixed>, 1: int} filters and per-page */
    protected function listParameters(Request $request, int $default = 25): array
    {
        $filters = $request->input('filter', []);
        if (! is_array($filters)) {
            $filters = [];
        }

        if ($request->filled('sort')) {
            $filters['sort'] = $request->string('sort')->toString();
        }

        return [$filters, min(max((int) $request->input('per_page', $default), 1), 100)];
    }
}
