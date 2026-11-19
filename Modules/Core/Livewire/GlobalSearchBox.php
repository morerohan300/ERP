<?php

declare(strict_types=1);

namespace Modules\Core\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;
use Modules\Core\Services\GlobalSearch;

final class GlobalSearchBox extends Component
{
    public string $term = '';

    /** @var Collection<string, Collection<int, array{title: string, subtitle: ?string, url: string}>> */
    public $results;

    public bool $open = false;

    public function mount(): void
    {
        $this->results = collect();
    }

    public function updatedTerm(): void
    {
        $this->open = mb_strlen(trim($this->term)) >= 2;
        $this->results = app(GlobalSearch::class)->search($this->term, auth()->user());
    }

    public function close(): void
    {
        $this->open = false;
        $this->term = '';
        $this->results = collect();
    }

    public function render(): View
    {
        return view('core::livewire.global-search-box');
    }
}
