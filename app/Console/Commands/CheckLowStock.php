<?php

namespace App\Console\Commands;

use App\Mail\LowStockNotificationMail;
use App\Models\Business;
use App\Models\Fabric;
use App\Models\ProductService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CheckLowStock extends Command
{
    protected $signature = 'inventory:check-low-stock';

    protected $description = 'Check inventory for low-stock items and send email alerts';

    public function handle(): int
    {
        $businesses = Business::all();

        foreach ($businesses as $business) {
            $products = ProductService::where('business_id', $business->id)
                ->where('type', 'product')
                ->whereNotNull('low_stock_threshold')
                ->whereColumn('quantity', '<=', 'low_stock_threshold')
                ->get();

            $fabrics = Fabric::where('business_id', $business->id)
                ->whereNotNull('low_stock_threshold')
                ->whereColumn('remaining_meters', '<=', 'low_stock_threshold')
                ->get();

            if ($products->isEmpty() && $fabrics->isEmpty()) {
                continue;
            }

            if (! $business->email) {
                continue;
            }

            try {
                Mail::to($business->email)
                    ->send(new LowStockNotificationMail($business, $products, $fabrics));
                $this->components->info("Low-stock alert sent to {$business->name}");
            } catch (\Exception $e) {
                $this->components->warn("Failed to send alert to {$business->name}: {$e->getMessage()}");
            }
        }

        return Command::SUCCESS;
    }
}
