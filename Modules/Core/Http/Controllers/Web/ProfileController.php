<?php

declare(strict_types=1);

namespace Modules\Core\Http\Controllers\Web;

use Illuminate\View\View;

final class ProfileController extends Controller
{
    public function show(): View
    {
        return view('profile.show');
    }
}
