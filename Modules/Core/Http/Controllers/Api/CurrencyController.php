<?php

declare(strict_types=1);

namespace Modules\Core\Http\Controllers\Api;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Core\Http\Resources\CurrencyResource;
use Modules\Core\Models\Currency;

final class CurrencyController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return CurrencyResource::collection(Currency::query()->orderBy('code')->get());
    }
}
