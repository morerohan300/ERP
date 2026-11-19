<?php

declare(strict_types=1);

namespace Modules\Core\Http\Controllers\Web;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Modules\Core\Jobs\RunBackup;
use Modules\Core\Models\Backup;
use Modules\Core\Services\BackupService;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class BackupController extends Controller
{
    public function __construct(private readonly BackupService $backups) {}

    public function index(): View
    {
        return view('core::backups.index', [
            'backups' => Backup::query()->with('creator')->latest()->paginate(20),
        ]);
    }

    public function store(): RedirectResponse
    {
        $backup = Backup::query()->create([
            'status' => 'queued',
            'created_by' => auth()->id(),
        ]);

        RunBackup::dispatch($backup);

        return $this->status('backups.index', __('Backup queued. It will appear here when complete.'));
    }

    public function download(Backup $backup): StreamedResponse
    {
        if (! $backup->path) {
            abort(404, __('This backup has no downloadable file.'));
        }

        return Storage::disk($backup->disk)->download($backup->path, basename($backup->path));
    }

    public function destroy(Backup $backup): RedirectResponse
    {
        Gate::authorize('core.backup.delete');
        $this->backups->delete($backup);

        return $this->status('backups.index', __('Backup deleted.'));
    }
}
