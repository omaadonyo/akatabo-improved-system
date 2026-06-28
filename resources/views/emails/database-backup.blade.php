<x-mail-layout title="Database Backup">
    <h2>{{ __('Database Backup Completed') }}</h2>
    <p>{{ __('A database backup has been successfully created.') }}</p>

    <div class="details">
        <div class="row">
            <span class="label">{{ __('Database') }}</span>
            <span class="value">{{ $dbName }}</span>
        </div>
        <div class="row">
            <span class="label">{{ __('Date') }}</span>
            <span class="value">{{ now()->format('d M Y H:i') }}</span>
        </div>
        <div class="row" style="border-bottom: none;">
            <span class="label">{{ __('File Size') }}</span>
            <span class="value">{{ number_format($fileSize / 1024, 2) }} KB</span>
        </div>
    </div>

    <p>{{ __('The backup file is attached to this email.') }}</p>
</x-mail-layout>
