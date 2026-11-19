<?php

declare(strict_types=1);

namespace Modules\Core\Http\Controllers\Web;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    use AuthorizesRequests;

    /** Flash a standard status message and redirect to a named route. */
    protected function status(string $route, string $message, mixed $parameter = null): RedirectResponse
    {
        return redirect()->route($route, $parameter)->with('status', $message);
    }
}
