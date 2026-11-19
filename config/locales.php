<?php

/*
|--------------------------------------------------------------------------
| Supported interface languages
|--------------------------------------------------------------------------
|
| `dir` drives the <html dir=""> attribute; RTL languages flip the layout
| automatically because views use Tailwind logical utilities (ms-, pe-...).
|
*/

return [
    'supported' => [
        'en' => ['name' => 'English', 'native' => 'English', 'dir' => 'ltr'],
        'ar' => ['name' => 'Arabic', 'native' => 'العربية', 'dir' => 'rtl'],
        'fr' => ['name' => 'French', 'native' => 'Français', 'dir' => 'ltr'],
        'es' => ['name' => 'Spanish', 'native' => 'Español', 'dir' => 'ltr'],
        'hi' => ['name' => 'Hindi', 'native' => 'हिन्दी', 'dir' => 'ltr'],
    ],

    'date_formats' => [
        'Y-m-d' => '2026-12-31',
        'd/m/Y' => '31/12/2026',
        'm/d/Y' => '12/31/2026',
        'd M Y' => '31 Dec 2026',
        'M d, Y' => 'Dec 31, 2026',
    ],
];
