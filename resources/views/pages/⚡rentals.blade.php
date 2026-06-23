<?php

use App\Mail\RentalReminderMail;
use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\ProductService;
use App\Models\Rental;
use Flux\Flux;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Rentals')] class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $statusFilter = '';

    public bool $showRentalModal = false;
    public ?int $editingRentalId = null;

    public bool $showViewRentalModal = false;
    public $viewingRental = null;

    public bool $showReminderModal = false;
    public string $reminderMessage = '';
    public $remindingRental = null;

    public ?int $customer_id = null;
    public ?int $room_id = null;
    public string $start_date = '';
    public string $end_date = '';
    public string $monthly_rent = '';
    public string $status = 'active';
    public string $notes = '';

    public function mount(): void
    {
        if (! activeBusiness()) {
            $this->redirect(route('onboarding', absolute: false), navigate: true);
        }
    }

    public function rentals()
    {
        return Rental::with(['customer', 'room', 'invoices'])
            ->where('business_id', activeBusinessId())
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->whereHas('customer', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
                  ->orWhereHas('room', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'));
            }))
            ->latest()
            ->paginate(10);
    }

    public function viewRental(Rental $rental): void
    {
        $this->viewingRental = $rental->load(['customer', 'room', 'invoices' => fn($q) => $q->latest(), 'invoices.payments']);
        $this->showViewRentalModal = true;
    }

    public function create(): void
    {
        $this->resetForm();
        $this->start_date = now()->format('Y-m-d');
        $this->showRentalModal = true;
    }

    public function edit(Rental $rental): void
    {
        $this->editingRentalId = $rental->id;
        $this->customer_id = $rental->customer_id;
        $this->room_id = $rental->room_id;
        $this->start_date = $rental->start_date->format('Y-m-d');
        $this->end_date = $rental->end_date?->format('Y-m-d') ?? '';
        $this->monthly_rent = (string) $rental->monthly_rent;
        $this->status = $rental->status;
        $this->notes = $rental->notes ?? '';
        $this->showRentalModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'room_id' => ['required', 'exists:products_services,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'monthly_rent' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $bizId = activeBusinessId();

        if ($this->editingRentalId) {
            $rental = Rental::where('business_id', $bizId)->findOrFail($this->editingRentalId);
            $rental->update([
                'customer_id' => $this->customer_id,
                'room_id' => $this->room_id,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date ?: null,
                'monthly_rent' => $this->monthly_rent,
                'status' => $this->status,
                'notes' => $this->notes ?: null,
                'updated_by' => auth()->id(),
            ]);

            ActivityLog::log('updated', 'Rental updated: ' . $rental->room->name . ' — ' . $rental->customer->name);
            Flux::toast(variant: 'success', text: __('Rental updated.'));
        } else {
            $room = ProductService::findOrFail($this->room_id);
            $rental = Rental::create([
                'business_id' => $bizId,
                'customer_id' => $this->customer_id,
                'room_id' => $this->room_id,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date ?: null,
                'monthly_rent' => $this->monthly_rent ?: ($room->selling_price ?? 0),
                'status' => 'active',
                'notes' => $this->notes ?: null,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            ActivityLog::log('created', 'Rental created: ' . $room->name . ' — ' . Customer::find($this->customer_id)->name);
            Flux::toast(variant: 'success', text: __('Rental created.'));
        }

        $this->resetForm();
        $this->showRentalModal = false;
    }

    public function endRental(Rental $rental): void
    {
        $rental->update([
            'status' => 'expired',
            'end_date' => now()->format('Y-m-d'),
            'updated_by' => auth()->id(),
        ]);

        ActivityLog::log('updated', 'Rental ended: ' . $rental->room->name . ' — ' . $rental->customer->name);
        Flux::toast(variant: 'success', text: __('Rental ended.'));
    }

    public function cancelRental(Rental $rental): void
    {
        $rental->update([
            'status' => 'cancelled',
            'end_date' => now()->format('Y-m-d'),
            'updated_by' => auth()->id(),
        ]);

        ActivityLog::log('updated', 'Rental cancelled: ' . $rental->room->name . ' — ' . $rental->customer->name);
        Flux::toast(variant: 'success', text: __('Rental cancelled.'));
    }

    public function generateInvoice(Rental $rental): void
    {
        $bizId = activeBusinessId();
        $last = Invoice::where('business_id', $bizId)->orderBy('id', 'desc')->first();
        $next = $last ? ((int) substr($last->invoice_number, -4)) + 1 : 1;
        $number = 'INV-' . str_pad($next, 4, '0', STR_PAD_LEFT);

        $periodLabel = now()->format('F Y');
        $invoice = Invoice::create([
            'business_id' => $bizId,
            'customer_id' => $rental->customer_id,
            'rental_id' => $rental->id,
            'invoice_number' => $number,
            'issue_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'subtotal' => $rental->monthly_rent,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total' => $rental->monthly_rent,
            'notes' => __('Rental period: :period — :room', ['period' => $periodLabel, 'room' => $rental->room->name]),
            'status' => 'sent',
            'paid_amount' => 0,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        $invoice->items()->create([
            'type' => 'office_rent',
            'item_id' => $rental->room_id,
            'description' => __(':room — Monthly rent (:period)', ['room' => $rental->room->name, 'period' => $periodLabel]),
            'quantity' => 1,
            'unit_price' => $rental->monthly_rent,
            'total' => $rental->monthly_rent,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        ActivityLog::log('created', 'Invoice ' . $number . ' generated for rental: ' . $rental->room->name);
        Flux::toast(variant: 'success', text: __('Invoice :number generated.', ['number' => $number]));

        $this->viewingRental = $rental->fresh()->load(['customer', 'room', 'invoices' => fn($q) => $q->latest(), 'invoices.payments']);
    }

    public function composeReminder(Rental $rental): void
    {
        $this->remindingRental = $rental->load(['customer', 'room']);
        $this->reminderMessage = __('Hi :name! Just a friendly reminder about your rent for :room. Please let me know if you have any questions. Thank you!', [
            'name' => $rental->customer->name,
            'room' => $rental->room->name,
        ]);
        $this->showReminderModal = true;
    }

    public function sendReminder(): void
    {
        $this->validate([
            'reminderMessage' => ['required', 'string', 'max:5000'],
        ]);

        $rental = $this->remindingRental;

        if (!$rental || !$rental->customer->email) {
            Flux::toast(variant: 'danger', text: __('Customer has no email address.'));
            return;
        }

        try {
            Mail::to($rental->customer->email)
                ->send(new \App\Mail\RentalReminderMail($rental, $this->reminderMessage));

            ActivityLog::log('sent', 'Rental reminder sent to ' . $rental->customer->name . ' for ' . $rental->room->name);
            Flux::toast(variant: 'success', text: __('Reminder sent to :name.', ['name' => $rental->customer->name]));
        } catch (\Exception $e) {
            Flux::toast(variant: 'danger', text: __('Failed to send reminder: :error', ['error' => $e->getMessage()]));
        }

        $this->showReminderModal = false;
        $this->reminderMessage = '';
        $this->remindingRental = null;
    }

    private function resetForm(): void
    {
        $this->reset(['customer_id', 'room_id', 'start_date', 'end_date', 'monthly_rent', 'status', 'notes', 'editingRentalId']);
    }

    public function getRoomsProperty()
    {
        return ProductService::where('business_id', activeBusinessId())
            ->where('type', 'office_rent')
            ->orderBy('name')
            ->get();
    }

    public function getCustomersProperty()
    {
        return Customer::where('business_id', activeBusinessId())
            ->orderBy('name')
            ->get();
    }
}; ?>

<div class="mx-auto" style="width: 90%;">
    <flux:heading size="xl">{{ __('Rentals') }}</flux:heading>
    <flux:subheading class="mt-1">{{ __('Manage room rentals, tenants, and recurring invoices.') }}</flux:subheading>

    {{-- Stats --}}
    @php
        $allRentals = Rental::where('business_id', activeBusinessId());
        $activeRentals = (clone $allRentals)->where('status', 'active');
        $activeCount = (clone $activeRentals)->count();
        $monthlyRevenue = (float) (clone $activeRentals)->sum('monthly_rent');
        $totalTenants = (clone $activeRentals)->distinct('customer_id')->count('customer_id');
        $overdueInvoices = Invoice::where('business_id', activeBusinessId())
            ->whereIn('status', ['sent', 'partial'])
            ->where('due_date', '<', now())
            ->sum('total') - Invoice::where('business_id', activeBusinessId())
            ->whereIn('status', ['sent', 'partial'])
            ->where('due_date', '<', now())
            ->sum('paid_amount');
    @endphp

    <div class="mt-6 mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <flux:card class="relative overflow-hidden p-4 bg-gradient-to-br from-indigo-50 to-blue-50 dark:from-indigo-950/30 dark:to-blue-950/20">
            <div class="absolute -bottom-4 -right-4 size-20 rounded-full bg-indigo-200/30 dark:bg-indigo-500/10 blur-2xl"></div>
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-xl bg-indigo-100 dark:bg-indigo-900/40 shadow-sm">
                    <flux:icon name="building" variant="micro" class="size-5 text-indigo-600 dark:text-indigo-400" />
                </div>
                <div>
                    <div class="text-xs font-medium text-indigo-600 dark:text-indigo-300">{{ __('Active Rentals') }}</div>
                    <div class="text-xl font-bold text-indigo-900 dark:text-indigo-100">{{ $activeCount }}</div>
                </div>
            </div>
        </flux:card>

        <flux:card class="relative overflow-hidden p-4 bg-gradient-to-br from-emerald-50 to-green-50 dark:from-emerald-950/30 dark:to-green-950/20">
            <div class="absolute -bottom-4 -right-4 size-20 rounded-full bg-emerald-200/30 dark:bg-emerald-500/10 blur-2xl"></div>
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-xl bg-emerald-100 dark:bg-emerald-900/40 shadow-sm">
                    <flux:icon name="currency-dollar" variant="micro" class="size-5 text-emerald-600 dark:text-emerald-400" />
                </div>
                <div>
                    <div class="text-xs font-medium text-emerald-600 dark:text-emerald-300">{{ __('Monthly Revenue') }}</div>
                    <div class="text-xl font-bold text-emerald-900 dark:text-emerald-100">${{ number_format($monthlyRevenue, 2) }}</div>
                </div>
            </div>
        </flux:card>

        <flux:card class="relative overflow-hidden p-4 bg-gradient-to-br from-violet-50 to-purple-50 dark:from-violet-950/30 dark:to-purple-950/20">
            <div class="absolute -bottom-4 -right-4 size-20 rounded-full bg-violet-200/30 dark:bg-violet-500/10 blur-2xl"></div>
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-xl bg-violet-100 dark:bg-violet-900/40 shadow-sm">
                    <flux:icon name="users" variant="micro" class="size-5 text-violet-600 dark:text-violet-400" />
                </div>
                <div>
                    <div class="text-xs font-medium text-violet-600 dark:text-violet-300">{{ __('Active Tenants') }}</div>
                    <div class="text-xl font-bold text-violet-900 dark:text-violet-100">{{ $totalTenants }}</div>
                </div>
            </div>
        </flux:card>

        <flux:card class="relative overflow-hidden p-4 bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-950/30 dark:to-orange-950/20">
            <div class="absolute -bottom-4 -right-4 size-20 rounded-full bg-amber-200/30 dark:bg-amber-500/10 blur-2xl"></div>
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-900/40 shadow-sm">
                    <flux:icon name="clock" variant="micro" class="size-5 text-amber-600 dark:text-amber-400" />
                </div>
                <div>
                    <div class="text-xs font-medium text-amber-600 dark:text-amber-300">{{ __('Overdue') }}</div>
                    <div class="text-xl font-bold text-amber-900 dark:text-amber-100">${{ number_format(max(0, $overdueInvoices), 2) }}</div>
                </div>
            </div>
        </flux:card>
    </div>

    {{-- Filters & Actions --}}
    <div class="mb-4 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <flux:input wire:model.live.debounce.300ms="search" :placeholder="__('Search tenant or room...')" icon="magnifying-glass" clearable class="w-72" />
            <flux:select wire:model.live="statusFilter" class="w-40">
                <option value="">{{ __('All Statuses') }}</option>
                <option value="active">{{ __('Active') }}</option>
                <option value="expired">{{ __('Expired') }}</option>
                <option value="cancelled">{{ __('Cancelled') }}</option>
            </flux:select>
        </div>
        <flux:button variant="primary" wire:click="create">
            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
            {{ __('New Rental') }}
        </flux:button>
    </div>

    {{-- Table --}}
    <flux:table :paginate="$this->rentals()">
        <flux:table.columns>
            <flux:table.column>{{ __('Tenant') }}</flux:table.column>
            <flux:table.column>{{ __('Room') }}</flux:table.column>
            <flux:table.column>{{ __('Duration') }}</flux:table.column>
            <flux:table.column>{{ __('Monthly Rent') }}</flux:table.column>
            <flux:table.column>{{ __('Status') }}</flux:table.column>
            <flux:table.column>{{ __('Invoices') }}</flux:table.column>
            <flux:table.column align="end">{{ __('Actions') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->rentals() as $rental)
                @php
                    $months = $rental->monthsElapsed();
                    $paid = $rental->totalPaid();
                    $expected = $rental->totalExpected();
                    $balance = $rental->balance();
                @endphp
                <flux:table.row :key="$rental->id">
                    <flux:table.cell>
                        <div class="flex items-center gap-2">
                            <flux:avatar size="xs" :name="$rental->customer->name" :initials="substr($rental->customer->name, 0, 2)" />
                            <span class="font-medium text-neutral-900 dark:text-white">{{ $rental->customer->name }}</span>
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="text-sm">{{ $rental->room->name }}</div>
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="text-sm">
                            {{ $rental->start_date->format('d M Y') }}
                            &rarr;
                            {{ $rental->end_date?->format('d M Y') ?? __('Ongoing') }}
                        </div>
                        <div class="text-xs text-neutral-500">{{ $months }} {{ Str::plural('month', $months) }}</div>
                    </flux:table.cell>
                    <flux:table.cell>
                        <span class="font-medium">${{ number_format($rental->monthly_rent, 2) }}</span>
                    </flux:table.cell>
                    <flux:table.cell>
                        @php
                            $statusColor = match($rental->status) {
                                'active' => 'emerald',
                                'expired' => 'neutral',
                                'cancelled' => 'red',
                                default => 'neutral',
                            };
                        @endphp
                        <flux:badge :color="$statusColor" size="sm">{{ ucfirst($rental->status) }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="text-sm">
                            <span class="{{ $paid >= $expected ? 'text-emerald-600' : 'text-amber-600' }}">
                                ${{ number_format($paid, 2) }}
                            </span>
                            / ${{ number_format($expected, 2) }}
                        </div>
                        @if ($balance > 0)
                            <div class="text-xs text-red-500">{{ __('Balance: $:amount', ['amount' => number_format($balance, 2)]) }}</div>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell align="end">
                        <div class="flex items-center justify-end gap-1">
                            <flux:button wire:click="viewRental({{ $rental->id }})" variant="ghost" size="sm" icon="eye" class="text-indigo-600! hover:text-indigo-800! dark:text-indigo-400! dark:hover:text-indigo-300!" />
                            <flux:button wire:click="edit({{ $rental->id }})" variant="ghost" size="sm" icon="pencil-square" class="text-sky-600! hover:text-sky-800! dark:text-sky-400! dark:hover:text-sky-300!" />
                            @if ($rental->status === 'active')
                                <flux:button wire:click="generateInvoice({{ $rental->id }})" variant="ghost" size="sm" icon="document-plus" class="text-emerald-600! hover:text-emerald-800! dark:text-emerald-400! dark:hover:text-emerald-300!" title="{{ __('Generate Invoice') }}" />
                            @endif
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="7">
                        <div class="flex flex-col items-center justify-center py-12 text-center">
                            <flux:icon name="building" class="size-8 text-neutral-300 dark:text-neutral-600" />
                            <flux:heading class="mt-2 text-neutral-400">{{ __('No rentals yet') }}</flux:heading>
                            <flux:subheading class="mt-1 text-neutral-400">{{ __('Create your first rental to start tracking.') }}</flux:subheading>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Create / Edit Modal --}}
    <flux:modal wire:model="showRentalModal" class="max-w-lg">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingRentalId ? __('Edit Rental') : __('New Rental') }}</flux:heading>
                <flux:subheading>{{ $editingRentalId ? __('Update the rental details.') : __('Register a new room rental.') }}</flux:subheading>
            </div>

            <flux:field>
                <flux:label>{{ __('Tenant') }}</flux:label>
                <flux:select wire:model="customer_id" required searchable>
                    <option value="">-- {{ __('Select tenant') }} --</option>
                    @foreach ($this->customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                    @endforeach
                </flux:select>
                <flux:error name="customer_id" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Room') }}</flux:label>
                <flux:select wire:model="room_id" required searchable>
                    <option value="">-- {{ __('Select room') }} --</option>
                    @foreach ($this->rooms as $room)
                        <option value="{{ $room->id }}">{{ $room->name }} @if($room->selling_price) (${{ number_format($room->selling_price, 2) }}/mo) @endif</option>
                    @endforeach
                </flux:select>
                <flux:error name="room_id" />
            </flux:field>

            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>{{ __('Start Date') }}</flux:label>
                    <flux:input wire:model="start_date" type="date" required />
                    <flux:error name="start_date" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('End Date') }}</flux:label>
                    <flux:input wire:model="end_date" type="date" />
                    <flux:error name="end_date" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>{{ __('Monthly Rent ($)') }}</flux:label>
                <flux:input wire:model="monthly_rent" type="number" step="0.01" required placeholder="0.00" />
                <flux:error name="monthly_rent" />
            </flux:field>

            @if ($editingRentalId)
                <flux:field>
                    <flux:label>{{ __('Status') }}</flux:label>
                    <flux:select wire:model="status" required>
                        <option value="active">{{ __('Active') }}</option>
                        <option value="expired">{{ __('Expired') }}</option>
                        <option value="cancelled">{{ __('Cancelled') }}</option>
                    </flux:select>
                </flux:field>
            @endif

            <flux:field>
                <flux:label>{{ __('Notes (optional)') }}</flux:label>
                <flux:textarea wire:model="notes" rows="3" placeholder="{{ __('Any additional notes...') }}" />
                <flux:error name="notes" />
            </flux:field>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="filled">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" type="submit">{{ $editingRentalId ? __('Update') : __('Create Rental') }}</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- View Rental Modal --}}
    <flux:modal wire:model="showViewRentalModal" class="max-w-2xl">
        @if ($viewingRental)
            <div class="space-y-6">
                <div class="flex items-start justify-between">
                    <div>
                        <flux:heading size="lg">{{ $viewingRental->room->name }}</flux:heading>
                        <flux:subheading>{{ __('Rental details and invoice history') }}</flux:subheading>
                    </div>
                    @php
                        $vStatusColor = match($viewingRental->status) {
                            'active' => 'emerald',
                            'expired' => 'neutral',
                            'cancelled' => 'red',
                            default => 'neutral',
                        };
                    @endphp
                    <flux:badge :color="$vStatusColor" size="sm">{{ ucfirst($viewingRental->status) }}</flux:badge>
                </div>

                <div class="grid grid-cols-2 gap-4 rounded-lg border border-neutral-200 bg-neutral-50 p-4 dark:border-neutral-700 dark:bg-neutral-900">
                    <div>
                        <flux:label>{{ __('Tenant') }}</flux:label>
                        <p class="mt-1 text-sm font-medium text-neutral-900 dark:text-white">{{ $viewingRental->customer->name }}</p>
                    </div>
                    <div>
                        <flux:label>{{ __('Monthly Rent') }}</flux:label>
                        <p class="mt-1 text-sm font-medium text-neutral-900 dark:text-white">${{ number_format($viewingRental->monthly_rent, 2) }}</p>
                    </div>
                    <div>
                        <flux:label>{{ __('Duration') }}</flux:label>
                        <p class="mt-1 text-sm font-medium text-neutral-900 dark:text-white">
                            {{ $viewingRental->start_date->format('d M Y') }}
                            &rarr;
                            {{ $viewingRental->end_date?->format('d M Y') ?? __('Ongoing') }}
                            <span class="text-neutral-500">({{ $viewingRental->monthsElapsed() }} {{ Str::plural('month', $viewingRental->monthsElapsed()) }})</span>
                        </p>
                    </div>
                    <div>
                        <flux:label>{{ __('Expected vs Paid') }}</flux:label>
                        <p class="mt-1 text-sm font-medium text-neutral-900 dark:text-white">
                            ${{ number_format($viewingRental->totalPaid(), 2) }} / ${{ number_format($viewingRental->totalExpected(), 2) }}
                            @if ($viewingRental->balance() > 0)
                                <span class="text-red-500">(${{ number_format($viewingRental->balance(), 2) }} {{ __('outstanding') }})</span>
                            @endif
                        </p>
                    </div>
                    @if ($viewingRental->notes)
                        <div class="col-span-2">
                            <flux:label>{{ __('Notes') }}</flux:label>
                            <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-400">{{ $viewingRental->notes }}</p>
                        </div>
                    @endif
                </div>

                {{-- Invoices --}}
                <div>
                    <div class="mb-3 flex items-center justify-between">
                        <flux:heading class="text-sm font-semibold">{{ __('Invoices') }}</flux:heading>
                        @if ($viewingRental->status === 'active')
                            <flux:button wire:click="generateInvoice({{ $viewingRental->id }})" size="sm" variant="primary">
                                <flux:icon name="document-plus" variant="micro" class="size-3.5" />
                                {{ __('Generate') }}
                            </flux:button>
                        @endif
                    </div>

                    @if ($viewingRental->invoices->isEmpty())
                        <p class="py-4 text-center text-sm text-neutral-500">{{ __('No invoices generated yet.') }}</p>
                    @else
                        <div class="space-y-2">
                            @foreach ($viewingRental->invoices as $inv)
                                @php
                                    $invStatusColor = match($inv->status) {
                                        'paid' => 'emerald',
                                        'sent' => 'blue',
                                        'partial' => 'amber',
                                        'overdue' => 'red',
                                        'draft' => 'neutral',
                                        'cancelled' => 'neutral',
                                        default => 'neutral',
                                    };
                                    $invPaid = (float) $inv->paid_amount;
                                    $invTotal = (float) $inv->total;
                                @endphp
                                <div class="flex items-center justify-between rounded-lg border border-neutral-200 bg-white p-3 dark:border-neutral-700 dark:bg-neutral-900">
                                    <div class="flex items-center gap-3">
                                        <flux:icon name="file-invoice" variant="micro" class="size-4 text-neutral-400" />
                                        <div>
                                            <span class="text-sm font-medium text-neutral-900 dark:text-white">{{ $inv->invoice_number }}</span>
                                            <span class="mx-1.5 text-xs text-neutral-400">&middot;</span>
                                            <span class="text-xs text-neutral-500">{{ $inv->issue_date->format('d M Y') }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <div class="text-right">
                                            <div class="text-sm font-medium text-neutral-900 dark:text-white">${{ number_format($invTotal, 2) }}</div>
                                            @if ($invPaid > 0)
                                                <div class="text-xs text-emerald-600">{{ __('Paid: $:amount', ['amount' => number_format($invPaid, 2)]) }}</div>
                                            @endif
                                        </div>
                                        <flux:badge :color="$invStatusColor" size="sm">{{ ucfirst($inv->status) }}</flux:badge>
                                        @if (!in_array($inv->status, ['paid', 'cancelled']))
                                            <flux:button wire:click="composeReminder({{ $viewingRental->id }})" variant="ghost" size="xs" icon="paper-airplane" class="text-indigo-400! hover:text-indigo-600!" title="{{ __('Send Reminder') }}" />
                                        @endif
                                        <flux:button :href="route('invoices.edit', $inv->id)" variant="ghost" size="xs" icon="arrow-top-right-on-square" class="text-neutral-400!" wire:navigate />
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="flex justify-between">
                    <div class="flex gap-2">
                        @if ($viewingRental->status === 'active')
                            <flux:button wire:click="endRental({{ $viewingRental->id }})" variant="filled" class="cursor-pointer">
                                {{ __('End Rental') }}
                            </flux:button>
                            <flux:button wire:click="cancelRental({{ $viewingRental->id }})" variant="ghost" class="cursor-pointer text-red-600!">
                                {{ __('Cancel') }}
                            </flux:button>
                        @endif
                    </div>
                    <flux:modal.close>
                        <flux:button variant="primary">{{ __('Close') }}</flux:button>
                    </flux:modal.close>
                </div>
            </div>
        @endif
    </flux:modal>

    {{-- Compose Reminder Modal --}}
    <flux:modal wire:model="showReminderModal" class="max-w-lg">
        @if ($remindingRental)
            <form wire:submit="sendReminder" class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('Send Friendly Reminder') }}</flux:heading>
                    <flux:subheading>{{ __('Send a custom reminder to :name about :room.', ['name' => $remindingRental->customer->name, 'room' => $remindingRental->room->name]) }}</flux:subheading>
                </div>

                <flux:field>
                    <flux:label>{{ __('Message') }}</flux:label>
                    <flux:textarea wire:model="reminderMessage" rows="6" required />
                    <flux:error name="reminderMessage" />
                </flux:field>

                <div class="flex justify-end gap-2">
                    <flux:modal.close>
                        <flux:button variant="filled">{{ __('Cancel') }}</flux:button>
                    </flux:modal.close>
                    <flux:button variant="primary" type="submit" icon="paper-airplane">
                        {{ __('Send Reminder') }}
                    </flux:button>
                </div>
            </form>
        @endif
    </flux:modal>
</div>
