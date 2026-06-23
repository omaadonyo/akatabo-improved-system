<?php

namespace App\Console\Commands;

use App\Mail\PaymentReminderMail;
use App\Models\Invoice;
use App\Models\Rental;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class ProcessRecurringInvoices extends Command
{
    protected $signature = 'invoices:process-recurring';

    protected $description = 'Generate recurring invoices, rental invoices, and send payment reminders';

    public function handle(): int
    {
        $this->generateRecurringCopies();
        $this->generateRentalInvoices();
        $this->sendPaymentReminders();

        return Command::SUCCESS;
    }

    protected function generateRecurringCopies(): void
    {
        $today = CarbonImmutable::today();

        $recurring = Invoice::with(['items', 'business', 'customer'])
            ->where('is_recurring', true)
            ->where('next_recurring_at', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->whereNull('recurring_ended_at')
                  ->orWhere('recurring_ended_at', '>=', $today);
            })
            ->get();

        foreach ($recurring as $invoice) {
            $this->components->task('Generating copy for ' . $invoice->invoice_number, function () use ($invoice) {
                $last = Invoice::where('business_id', $invoice->business_id)
                    ->orderBy('id', 'desc')->first();
                $next = $last ? ((int) substr($last->invoice_number, -4)) + 1 : 1;
                $newNumber = 'INV-' . str_pad($next, 4, '0', STR_PAD_LEFT);

                $newDueDate = $invoice->due_date
                    ? CarbonImmutable::parse($invoice->due_date->format('Y-m-d'))->addMonth()
                    : CarbonImmutable::today()->addMonth();

                $copy = Invoice::create([
                    'business_id' => $invoice->business_id,
                    'customer_id' => $invoice->customer_id,
                    'invoice_number' => $newNumber,
                    'issue_date' => $today->format('Y-m-d'),
                    'due_date' => $newDueDate->format('Y-m-d'),
                    'subtotal' => $invoice->subtotal,
                    'discount_type' => $invoice->discount_type,
                    'discount_value' => $invoice->discount_value,
                    'discount_amount' => $invoice->discount_amount,
                    'tax_name' => $invoice->tax_name,
                    'tax_rate' => $invoice->tax_rate,
                    'tax_amount' => $invoice->tax_amount,
                    'total' => $invoice->total,
                    'notes' => $invoice->notes,
                    'is_recurring' => true,
                    'recurring_frequency' => 'monthly',
                    'next_recurring_at' => $newDueDate->addMonth()->format('Y-m-d'),
                    'status' => 'sent',
                    'paid_amount' => 0,
                    'created_by' => $invoice->created_by,
                    'updated_by' => $invoice->updated_by,
                ]);

                foreach ($invoice->items as $item) {
                    $copy->items()->create([
                        'type' => $item->type,
                        'item_id' => $item->item_id,
                        'description' => $item->description,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'total' => $item->total,
                        'created_by' => $invoice->created_by,
                        'updated_by' => $invoice->updated_by,
                    ]);
                }

                $nextDate = $newDueDate->addMonth()->format('Y-m-d');
                $invoice->update([
                    'next_recurring_at' => $nextDate,
                    'updated_by' => $invoice->created_by,
                ]);
            });
        }

        $count = $recurring->count();
        if ($count > 0) {
            $this->components->info("Generated {$count} recurring invoice(s).");
        }
    }

    protected function sendPaymentReminders(): void
    {
        $targetDate = CarbonImmutable::today()->addDays(10);

        $invoices = Invoice::with(['customer', 'business'])
            ->whereDate('due_date', $targetDate)
            ->whereRaw('(total - paid_amount) > 0')
            ->whereHas('customer', fn($q) => $q->whereNotNull('email'))
            ->get();

        foreach ($invoices as $invoice) {
            $this->components->task('Sending reminder for ' . $invoice->invoice_number, function () use ($invoice) {
                try {
                    Mail::to($invoice->customer->email)
                        ->send(new PaymentReminderMail($invoice));
                } catch (\Exception $e) {
                    $this->components->warn('Failed to send: ' . $e->getMessage());
                }
            });
        }

        $count = $invoices->count();
        if ($count > 0) {
            $this->components->info("Sent {$count} payment reminder(s).");
        }
    }

    protected function generateRentalInvoices(): void
    {
        $today = CarbonImmutable::today();
        $startOfMonth = $today->startOfMonth()->format('Y-m-d');
        $endOfMonth = $today->endOfMonth()->format('Y-m-d');

        $rentals = Rental::with(['customer', 'room', 'business'])
            ->where('status', 'active')
            ->where('start_date', '<=', $today)
            ->get();

        $generated = 0;

        foreach ($rentals as $rental) {
            $hasInvoiceThisMonth = Invoice::where('rental_id', $rental->id)
                ->whereDate('issue_date', '>=', $startOfMonth)
                ->whereDate('issue_date', '<=', $endOfMonth)
                ->exists();

            if ($hasInvoiceThisMonth) {
                continue;
            }

            $last = Invoice::where('business_id', $rental->business_id)
                ->orderBy('id', 'desc')->first();
            $next = $last ? ((int) substr($last->invoice_number, -4)) + 1 : 1;
            $number = 'INV-' . str_pad($next, 4, '0', STR_PAD_LEFT);

            $periodLabel = $today->format('F Y');

            $invoice = Invoice::create([
                'business_id' => $rental->business_id,
                'customer_id' => $rental->customer_id,
                'rental_id' => $rental->id,
                'invoice_number' => $number,
                'issue_date' => $today->format('Y-m-d'),
                'due_date' => $today->addDays(7)->format('Y-m-d'),
                'subtotal' => $rental->monthly_rent,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total' => $rental->monthly_rent,
                'notes' => 'Rental period: ' . $periodLabel . ' — ' . $rental->room->name,
                'status' => 'sent',
                'paid_amount' => 0,
                'created_by' => 1,
                'updated_by' => 1,
            ]);

            $invoice->items()->create([
                'type' => 'office_rent',
                'item_id' => $rental->room_id,
                'description' => $rental->room->name . ' — Monthly rent (' . $periodLabel . ')',
                'quantity' => 1,
                'unit_price' => $rental->monthly_rent,
                'total' => $rental->monthly_rent,
                'created_by' => 1,
                'updated_by' => 1,
            ]);

            $generated++;
        }

        if ($generated > 0) {
            $this->components->info("Generated {$generated} rental invoice(s).");
        }
    }
}
