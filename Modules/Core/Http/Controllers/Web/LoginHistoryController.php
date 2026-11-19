<?php

declare(strict_types=1);

namespace Modules\Core\Http\Controllers\Web;

use Illuminate\View\View;

final class LoginHistoryController extends Controller
{
    public function index(): View
    {
        return view('core::login-history.index');
    }
}
