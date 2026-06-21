<?php

use Illuminate\Support\Facades\Artisan;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Backup')] class extends Component {

    public string $mailTo = 'owacomputer@gmail.com';

    public ?string $lastBackup = null;

    public ?string $lastBackupSize = null;

    public array $history = [];

    public function mount(): void
    {
        $this->mailTo = config('backup.mail_to', 'owacomputer@gmail.com');
        $this->refreshHistory();
    }

    public function refreshHistory(): void
    {
        $dir = storage_path('app/backups');
        if (! is_dir($dir)) {
            $this->history = [];
            $this->lastBackup = null;
            $this->lastBackupSize = null;
            return;
        }

        $files = collect(scandir($dir))
            ->filter(fn ($f) => str_ends_with($f, '.sql'))
            ->map(fn ($f) => [
                'name' => $f,
                'path' => $dir . '/' . $f,
                'size' => filesize($dir . '/' . $f),
                'date' => filemtime($dir . '/' . $f),
            ])
            ->sortByDesc('date')
            ->values();

        $this->history = $files->toArray();

        $latest = $files->first();
        if ($latest) {
            $this->lastBackup = date('Y-m-d H:i:s', $latest['date']);
            $this->lastBackupSize = round($latest['size'] / 1024 / 1024, 2) . ' MB';
        } else {
            $this->lastBackup = null;
            $this->lastBackupSize = null;
        }
    }

    public function runBackup(): void
    {
        $exitCode = Artisan::call('backup:database', ['--mail-to' => $this->mailTo]);

        if ($exitCode === 0) {
            $this->refreshHistory();
            Flux::toast(variant: 'success', text: __('Backup completed and emailed.'));
        } else {
            Flux::toast(variant: 'danger', text: Artisan::output());
        }
    }

    public function saveSettings(): void
    {
        $this->validate(['mailTo' => 'required|email']);
        Flux::toast(variant: 'success', text: __('Settings saved.'));
    }
}; ?>

<div class="mx-auto" style="width: 80%;">
    <div class="mb-6">
        <flux:heading size="xl">{{ __('Backup') }}</flux:heading>
        <flux:subheading class="mt-1">{{ __('Manage automatic database backups.') }}</flux:subheading>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Status Card --}}
        <flux:card class="relative overflow-hidden p-5 bg-gradient-to-br from-sky-50 to-indigo-50 dark:from-sky-950/30 dark:to-indigo-950/20">
            <div class="absolute -bottom-4 -right-4 size-24 rounded-full bg-sky-200/30 dark:bg-sky-500/10 blur-2xl"></div>
            <div class="mb-4 flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-xl bg-sky-100 dark:bg-sky-900/40 shadow-sm shadow-sky-200/50 dark:shadow-sky-900/30">
                    <flux:icon name="server-stack" variant="solid" class="size-5 text-sky-600 dark:text-sky-400" />
                </div>
                <flux:heading size="sm" class="text-sky-800 dark:text-sky-200">{{ __('Backup Status') }}</flux:heading>
            </div>
            <div class="mt-4 space-y-3">
                <div class="flex items-center justify-between rounded-lg bg-white/60 dark:bg-neutral-800/40 px-3 py-2 text-sm">
                    <span class="text-neutral-500 dark:text-neutral-400">{{ __('Last Backup') }}</span>
                    <span class="font-medium text-neutral-900 dark:text-white">{{ $lastBackup ?? __('Never') }}</span>
                </div>
                <div class="flex items-center justify-between rounded-lg bg-white/60 dark:bg-neutral-800/40 px-3 py-2 text-sm">
                    <span class="text-neutral-500 dark:text-neutral-400">{{ __('Size') }}</span>
                    <span class="font-medium text-neutral-900 dark:text-white">{{ $lastBackupSize ?? '—' }}</span>
                </div>
                <div class="flex items-center justify-between rounded-lg bg-white/60 dark:bg-neutral-800/40 px-3 py-2 text-sm">
                    <span class="text-neutral-500 dark:text-neutral-400">{{ __('Schedule') }}</span>
                    <span class="font-medium text-neutral-900 dark:text-white">{{ __('Daily at midnight') }}</span>
                </div>
                <div class="flex items-center justify-between rounded-lg bg-white/60 dark:bg-neutral-800/40 px-3 py-2 text-sm">
                    <span class="text-neutral-500 dark:text-neutral-400">{{ __('Recipient') }}</span>
                    <span class="font-medium text-neutral-900 dark:text-white">{{ $mailTo }}</span>
                </div>
            </div>

            <flux:button wire:click="runBackup" variant="primary" icon="arrow-path" class="mt-4 w-full cursor-pointer">
                {{ __('Run Backup Now') }}
            </flux:button>
        </flux:card>

        {{-- Settings Card --}}
        <flux:card class="relative overflow-hidden p-5 bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-950/30 dark:to-teal-950/20">
            <div class="absolute -bottom-4 -right-4 size-24 rounded-full bg-emerald-200/30 dark:bg-emerald-500/10 blur-2xl"></div>
            <div class="mb-4 flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-xl bg-emerald-100 dark:bg-emerald-900/40 shadow-sm shadow-emerald-200/50 dark:shadow-emerald-900/30">
                    <flux:icon name="envelope" variant="solid" class="size-5 text-emerald-600 dark:text-emerald-400" />
                </div>
                <flux:heading size="sm" class="text-emerald-800 dark:text-emerald-200">{{ __('Email Settings') }}</flux:heading>
            </div>
            <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ __('The SQL dump will be sent to this address after each backup.') }}</p>

            <div class="mt-4 space-y-4">
                <flux:field>
                    <flux:label>{{ __('Send to') }}</flux:label>
                    <flux:input wire:model="mailTo" type="email" placeholder="admin@example.com" />
                    <flux:error name="mailTo" />
                </flux:field>

                <flux:button wire:click="saveSettings" variant="primary" class="cursor-pointer">
                    {{ __('Save') }}
                </flux:button>
            </div>
        </flux:card>
    </div>

    {{-- Backup History --}}
    <flux:card class="mt-6 p-5 bg-gradient-to-br from-white to-neutral-50 dark:from-neutral-900 dark:to-neutral-950">
        <div class="mb-4 flex items-center gap-3">
            <div class="flex size-9 items-center justify-center rounded-xl bg-violet-100 dark:bg-violet-900/40 shadow-sm shadow-violet-200/50 dark:shadow-violet-900/30">
                <flux:icon name="clock" variant="solid" class="size-4.5 text-violet-600 dark:text-violet-400" />
            </div>
            <flux:heading size="sm" class="text-violet-800 dark:text-violet-200">{{ __('Backup History') }}</flux:heading>
        </div>

        <div>
            @if (empty($history))
                <p class="py-6 text-center text-sm text-neutral-500 dark:text-neutral-400">{{ __('No backups recorded yet.') }}</p>
            @else
                <div class="divide-y divide-neutral-100 dark:divide-neutral-800">
                    @foreach ($history as $entry)
                        <div class="flex items-center justify-between py-2.5 text-sm transition-colors hover:bg-neutral-50 dark:hover:bg-neutral-800/30 px-2 -mx-2 rounded-lg">
                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                <div class="shrink-0 flex size-8 items-center justify-center rounded-lg bg-neutral-100 dark:bg-neutral-800">
                                    <flux:icon name="document" variant="solid" class="size-4 text-neutral-500 dark:text-neutral-400" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-medium text-neutral-900 dark:text-white">{{ $entry['name'] }}</p>
                                    <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ date('Y-m-d H:i:s', $entry['date']) }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 ps-3 shrink-0">
                                <span class="text-xs font-medium text-neutral-500 dark:text-neutral-400 bg-neutral-100 dark:bg-neutral-800 px-2 py-0.5 rounded-full">{{ round($entry['size'] / 1024, 1) }} KB</span>
                                <a href="{{ route('backups.download', ['filename' => $entry['name']]) }}"
                                   class="inline-flex size-8 items-center justify-center rounded-lg text-neutral-400 hover:bg-sky-50 hover:text-sky-600 dark:hover:bg-sky-900/30 dark:hover:text-sky-400 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </flux:card>
</div>
