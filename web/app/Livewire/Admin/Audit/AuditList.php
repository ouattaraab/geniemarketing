<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Audit;

use App\Models\AuditLog;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin', ['title' => 'Journal d\'audit'])]
#[Title('Audit — GM Admin')]
class AuditList extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $action = 'all';

    #[Url]
    public ?int $userId = null;

    public function mount(): void
    {
        abort_unless(
            auth()->user()?->hasAnyRole(['adm', 'sup']),
            403,
        );
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function exportCsv()
    {
        abort_unless(auth()->user()->hasAnyRole(['adm', 'sup']), 403);

        $filename = 'audit-logs-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Utilisateur', 'Action', 'Objet', 'IP', 'Changements']);

            AuditLog::query()->with('user')->orderByDesc('id')->chunk(500, function ($logs) use ($handle): void {
                foreach ($logs as $log) {
                    fputcsv($handle, array_map(
                        [$this, 'neutraliseCsvFormula'],
                        [
                            $log->created_at?->format('Y-m-d H:i:s'),
                            $log->user?->email ?? '—',
                            $log->action,
                            $log->object_type ? class_basename($log->object_type).'#'.$log->object_id : '—',
                            $log->ip ?? '—',
                            $log->changes ? json_encode($log->changes, JSON_UNESCAPED_UNICODE) : '',
                        ]
                    ));
                }
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Préfixe les champs qui commencent par =, +, -, @, tab ou CR d'une
     * simple quote pour neutraliser les formules Excel/LibreOffice/Numbers
     * (CSV injection / DDE attack). Les valeurs non-dangereuses sont
     * conservées telles quelles.
     */
    private function neutraliseCsvFormula(mixed $value): string
    {
        $str = (string) ($value ?? '');
        return preg_match("/^[=+\-@\t\r]/", $str) ? "'".$str : $str;
    }

    public function logs(): LengthAwarePaginator
    {
        $query = AuditLog::query()
            ->with('user')
            ->latest('id');

        if ($this->search !== '') {
            $query->where(function ($q): void {
                $q->where('action', 'like', '%'.$this->search.'%')
                    ->orWhere('object_type', 'like', '%'.$this->search.'%')
                    ->orWhere('ip', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->action !== 'all') {
            $query->where('action', 'like', $this->action.'%');
        }

        if ($this->userId) {
            $query->where('user_id', $this->userId);
        }

        return $query->paginate(30);
    }

    public function render(): View
    {
        return view('livewire.admin.audit.audit-list', [
            'logs' => $this->logs(),
        ]);
    }
}
