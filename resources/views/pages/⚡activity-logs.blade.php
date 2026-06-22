<?php

use App\Models\ActivityLog;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Activity Logs')] class extends Component {
    use WithPagination;

    public string $eventFilter = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public function with(): array
    {
        $businessId = activeBusinessId();

        $logs = ActivityLog::with('user')
            ->where('business_id', $businessId)
            ->when($this->eventFilter, fn($q) => $q->where('action', $this->eventFilter))
            ->when($this->dateFrom, fn($q) => $q->where('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->where('created_at', '<=', $this->dateTo . ' 23:59:59'))
            ->latest('created_at')
            ->paginate(20);

        $allLogs = ActivityLog::where('business_id', $businessId);

        return [
            'logs' => $logs,
            'stats' => [
                'total' => (clone $allLogs)->count(),
                'today' => (clone $allLogs)->whereDate('created_at', today())->count(),
                'logins' => (clone $allLogs)->where('action', 'login')->count(),
                'uniqueUsers' => (clone $allLogs)->distinct('user_id')->count('user_id'),
            ],
            'eventTypes' => (clone $allLogs)->selectRaw('action, COUNT(*) as count')
                ->groupBy('action')
                ->orderByDesc('count')
                ->get(),
        ];
    }
}; ?>

<div class="mx-auto" style="width: 90%;">
    {{-- Header --}}
    <div class="mb-6">
        <flux:heading size="xl">{{ __('Activity Logs') }}</flux:heading>
        <flux:subheading class="mt-1">{{ __('Monitor all user activities, login sessions, and system events.') }}</flux:subheading>
    </div>

    {{-- Stats Cards --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <flux:card class="relative overflow-hidden p-4 bg-gradient-to-br from-indigo-50 to-blue-50 dark:from-indigo-950/30 dark:to-blue-950/20">
            <div class="absolute -bottom-4 -right-4 size-20 rounded-full bg-indigo-200/30 dark:bg-indigo-500/10 blur-2xl"></div>
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-xl bg-indigo-100 dark:bg-indigo-900/40 shadow-sm">
                    <flux:icon name="clipboard-document-list" variant="solid" class="size-5 text-indigo-600 dark:text-indigo-400" />
                </div>
                <div>
                    <div class="text-xs font-medium text-indigo-600 dark:text-indigo-300">{{ __('Total Activities') }}</div>
                    <div class="text-xl font-bold text-indigo-900 dark:text-indigo-100">{{ $stats['total'] }}</div>
                </div>
            </div>
        </flux:card>

        <flux:card class="relative overflow-hidden p-4 bg-gradient-to-br from-emerald-50 to-green-50 dark:from-emerald-950/30 dark:to-green-950/20">
            <div class="absolute -bottom-4 -right-4 size-20 rounded-full bg-emerald-200/30 dark:bg-emerald-500/10 blur-2xl"></div>
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-xl bg-emerald-100 dark:bg-emerald-900/40 shadow-sm">
                    <flux:icon name="calendar-days" variant="solid" class="size-5 text-emerald-600 dark:text-emerald-400" />
                </div>
                <div>
                    <div class="text-xs font-medium text-emerald-600 dark:text-emerald-300">{{ __('Today') }}</div>
                    <div class="text-xl font-bold text-emerald-900 dark:text-emerald-100">{{ $stats['today'] }}</div>
                </div>
            </div>
        </flux:card>

        <flux:card class="relative overflow-hidden p-4 bg-gradient-to-br from-violet-50 to-purple-50 dark:from-violet-950/30 dark:to-purple-950/20">
            <div class="absolute -bottom-4 -right-4 size-20 rounded-full bg-violet-200/30 dark:bg-violet-500/10 blur-2xl"></div>
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-xl bg-violet-100 dark:bg-violet-900/40 shadow-sm">
                    <flux:icon name="arrow-right-end-on-rectangle" variant="solid" class="size-5 text-violet-600 dark:text-violet-400" />
                </div>
                <div>
                    <div class="text-xs font-medium text-violet-600 dark:text-violet-300">{{ __('Total Logins') }}</div>
                    <div class="text-xl font-bold text-violet-900 dark:text-violet-100">{{ $stats['logins'] }}</div>
                </div>
            </div>
        </flux:card>

        <flux:card class="relative overflow-hidden p-4 bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-950/30 dark:to-orange-950/20">
            <div class="absolute -bottom-4 -right-4 size-20 rounded-full bg-amber-200/30 dark:bg-amber-500/10 blur-2xl"></div>
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-900/40 shadow-sm">
                    <flux:icon name="users" variant="solid" class="size-5 text-amber-600 dark:text-amber-400" />
                </div>
                <div>
                    <div class="text-xs font-medium text-amber-600 dark:text-amber-300">{{ __('Active Users') }}</div>
                    <div class="text-xl font-bold text-amber-900 dark:text-amber-100">{{ $stats['uniqueUsers'] }}</div>
                </div>
            </div>
        </flux:card>
    </div>

    {{-- Filters --}}
    <div class="mb-4 flex flex-wrap items-center gap-3">
        <flux:select wire:model.live="eventFilter" class="w-44">
            <option value="">{{ __('All Events') }}</option>
            @foreach ($eventTypes as $et)
                <option value="{{ $et->action }}">{{ ucfirst($et->action) }} ({{ $et->count }})</option>
            @endforeach
        </flux:select>
        <flux:input wire:model.live="dateFrom" type="date" class="w-40" placeholder="{{ __('From') }}" />
        <flux:input wire:model.live="dateTo" type="date" class="w-40" placeholder="{{ __('To') }}" />
        @if ($eventFilter || $dateFrom || $dateTo)
            <flux:button wire:click="$set('eventFilter', ''); $set('dateFrom', ''); $set('dateTo', '')" variant="ghost" size="sm" icon="x-mark" class="text-red-500!">
                {{ __('Clear') }}
            </flux:button>
        @endif
    </div>

    {{-- Activity Table --}}
    <flux:table :paginate="$logs">
        <flux:table.columns>
            <flux:table.column>{{ __('Event') }}</flux:table.column>
            <flux:table.column>{{ __('User') }}</flux:table.column>
            <flux:table.column>{{ __('Description') }}</flux:table.column>
            <flux:table.column>{{ __('IP Address') }}</flux:table.column>
            <flux:table.column>{{ __('Date & Time') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($logs as $log)
                @php
                    $eventStyle = match($log->action) {
                        'login' => ['color' => 'blue', 'icon' => 'arrow-right-end-on-rectangle', 'bg' => 'bg-blue-50 dark:bg-blue-900/20'],
                        'logout' => ['color' => 'amber', 'icon' => 'arrow-left-end-on-rectangle', 'bg' => 'bg-amber-50 dark:bg-amber-900/20'],
                        'created' => ['color' => 'emerald', 'icon' => 'plus-circle', 'bg' => 'bg-emerald-50 dark:bg-emerald-900/20'],
                        'updated' => ['color' => 'indigo', 'icon' => 'pencil-square', 'bg' => 'bg-indigo-50 dark:bg-indigo-900/20'],
                        'deleted' => ['color' => 'red', 'icon' => 'trash', 'bg' => 'bg-red-50 dark:bg-red-900/20'],
                        'converted' => ['color' => 'violet', 'icon' => 'arrow-path', 'bg' => 'bg-violet-50 dark:bg-violet-900/20'],
                        'exported' => ['color' => 'sky', 'icon' => 'arrow-down-tray', 'bg' => 'bg-sky-50 dark:bg-sky-900/20'],
                        'recorded' => ['color' => 'teal', 'icon' => 'credit-card', 'bg' => 'bg-teal-50 dark:bg-teal-900/20'],
                        default => ['color' => 'neutral', 'icon' => 'clock', 'bg' => 'bg-neutral-50 dark:bg-neutral-800'],
                    };
                @endphp
                <flux:table.row>
                    <flux:table.cell>
                        <flux:badge :color="$eventStyle['color']" :icon="$eventStyle['icon']" size="sm">
                            {{ ucfirst($log->action) }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="flex items-center gap-2">
                            <flux:avatar size="xs" :name="$log->user?->name ?? __('System')" :initials="substr($log->user?->name ?? 'S', 0, 2)" />
                            <span class="text-sm font-medium text-neutral-900 dark:text-white">{{ $log->user?->name ?? __('System') }}</span>
                        </div>
                    </flux:table.cell>
                    <flux:table.cell class="max-w-xs text-sm text-neutral-600 dark:text-neutral-400 truncate" title="{{ $log->description }}">
                        {{ $log->description }}
                    </flux:table.cell>
                    <flux:table.cell>
                        <code class="rounded bg-neutral-100 px-1.5 py-0.5 text-xs font-mono text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400">{{ $log->ip_address ?? '—' }}</code>
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap text-xs text-neutral-500">
                        <div>{{ $log->created_at->format('d M Y') }}</div>
                        <div class="text-neutral-400">{{ $log->created_at->format('H:i:s') }}</div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="5">
                        <div class="flex flex-col items-center py-12 text-center">
                            <flux:icon name="clipboard-document" class="size-8 text-neutral-300 dark:text-neutral-600" />
                            <flux:heading class="mt-2 text-neutral-400">{{ __('No activity logs yet') }}</flux:heading>
                            <flux:subheading class="mt-1 text-neutral-400">{{ __('Activities will appear here as users interact with the system.') }}</flux:subheading>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>
