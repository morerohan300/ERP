<?php

declare(strict_types=1);

namespace Modules\Core\Http\Controllers\Web;

use App\Support\Tenancy\CurrentCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class AuditLogController extends Controller
{
    public function index(): View
    {
        return view('core::audit-log.index');
    }

    public function export(Request $request): StreamedResponse
    {
        return response()->stream(function () use ($request): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Event', 'Description', 'Causer']);

            DB::table('activity_log')
                ->when(app(CurrentCompany::class)->id(), fn ($q, $id) => $q->where('company_id', $id))
                ->when($request->input('event'), fn ($q, $event) => $q->where('event', $event))
                ->latest('created_at')
                ->orderBy('id')
                ->chunk(500, function ($rows) use ($handle): void {
                    foreach ($rows as $row) {
                        fputcsv($handle, [$row->created_at, $row->event, $row->description, $row->causer_type.'#'.$row->causer_id]);
                    }
                });

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="audit-log.csv"',
        ]);
    }
}
