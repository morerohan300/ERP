<?php

declare(strict_types=1);

namespace Modules\Core\Http\Controllers\Web;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Core\Exports\UsersExport;
use Modules\Core\Http\Requests\StoreDataTransferRequest;
use Modules\Core\Jobs\ProcessDataImport;
use Modules\Core\Models\DataTransfer;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class DataTransferController extends Controller
{
    public function index(): View
    {
        return view('core::data-transfers.index', [
            'transfers' => DataTransfer::query()->with('creator')->latest()->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('core::data-transfers.create', [
            'entities' => array_keys(ProcessDataImport::$importers),
        ]);
    }

    public function store(StoreDataTransferRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $path = null;
        if (($file = $request->file('file')) !== null) {
            $path = $file->store('data-transfers', 'local');
        }

        $transfer = DataTransfer::query()->create([
            'direction' => $data['direction'],
            'module' => $data['module'],
            'entity' => $data['entity'],
            'format' => $data['format'],
            'file_path' => $path,
            'original_name' => $file?->getClientOriginalName(),
            'status' => $data['direction'] === 'import' ? 'queued' : 'completed',
        ]);

        if ($data['direction'] === 'import') {
            ProcessDataImport::dispatch($transfer);
            $message = __('Import queued. You will be notified when it finishes.');
        } else {
            $filename = "{$data['entity']}-".now()->toDateString().'.'.$data['format'];
            Excel::store(new UsersExport(User::query()->inCurrentCompany()), "data-transfers/{$filename}", 'local');
            $transfer->update(['file_path' => "data-transfers/{$filename}", 'original_name' => $filename]);
            $message = __('Export ready to download.');
        }

        return $this->status('data-transfers.index', $message);
    }

    public function download(DataTransfer $transfer): BinaryFileResponse
    {
        if (! $transfer->file_path || ! Storage::disk('local')->exists($transfer->file_path)) {
            abort(404, __('The file for this transfer is no longer available.'));
        }

        return Storage::disk('local')->download($transfer->file_path, $transfer->original_name ?: basename($transfer->file_path));
    }
}
