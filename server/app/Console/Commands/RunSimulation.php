<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class RunSimulation extends Command
{
    protected $signature = 'simulation:payments {--count=5 : Number of payments to simulate}';
    protected $description = 'Simulate payment transactions';

    public function handle()
    {
        $count = (int) $this->option('count');
        
        $this->info("Starting payment simulation for {$count} transactions...");
        
        $businesses = Business::take(5)->get();
        
        if ($businesses->isEmpty()) {
            $this->error('No businesses found. Please create some businesses first.');
            return 1;
        }

        $this->table(
            ['Business', 'Package', 'Amount', 'Status', 'Reference'],
            $this->generatePayments($businesses, $count)
        );

        $this->info("\nPayment simulation completed!");
        return 0;
    }

    private function generatePayments($businesses, $count)
    {
        $payments = [];
        $packages = [
            'Basic' => 0,
            'Bronze' => 200,
            'Silver' => 500,
            'Gold' => 1000
        ];

        for ($i = 0; $i < $count; $i++) {
            $business = $businesses->random();
            $package = array_rand($packages);
            $amount = $packages[$package];
            $status = $this->getRandomStatus();
            $reference = 'PAY-' . strtoupper(Str::random(8));

            // Create payment record
            Payment::create([
                'business_id' => $business->id,
                'user_id' => $business->user_id,
                'amount' => $amount,
                'package' => $package,
                'status' => $status,
                'reference' => $reference,
                'payment_method' => 'simulation',
                'payment_date' => now(),
            ]);

            // Update business package if payment was successful
            if ($status === 'completed') {
                $business->update([
                    'package_type' => $package,
                    'subscription_status' => 'active',
                    'next_billing_date' => now()->addMonth(),
                ]);
            }

            $payments[] = [
                'business' => $business->name,
                'package' => $package,
                'amount' => 'R' . number_format($amount, 2),
                'status' => $status,
                'reference' => $reference,
            ];
        }

        return $payments;
    }

    private function getRandomStatus()
    {
        $statuses = ['pending', 'completed', 'failed', 'refunded'];
        $weights = [20, 60, 15, 5]; // 60% chance of completed
        
        $total = array_sum($weights);
        $rand = mt_rand(1, $total);
        $current = 0;
        
        foreach ($statuses as $i => $status) {
            $current += $weights[$i];
            if ($rand <= $current) {
                return $status;
            }
        }
        
        return 'completed';
    }
}