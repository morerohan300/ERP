<?php

declare(strict_types=1);

use Modules\Core\Services\PermissionService;

it('expands wildcard permission patterns from module definitions', function (): void {
    $permissions = app(PermissionService::class)->expand([
        'core.*.view',
        'core.branch.*',
    ]);

    expect($permissions)
        ->toContain('core.branch.view')
        ->toContain('core.branch.create')
        ->toContain('core.tax.view')
        ->not->toContain('core.branch.delete.anything');
});
