<?php

namespace App\Console\Commands;

use App\Models\ProgressItem;
use Illuminate\Console\Command;

class SeedProgressItems extends Command
{
    protected $signature = 'progress:seed';

    protected $description = 'Seed progress items for client tasks';

    public function handle(): int
    {
        $items = [
            ['title' => 'Order alphabetical sorting', 'description' => 'Sort orders and lists alphabetically'],
            ['title' => 'Invoice format fix + remaining amount', 'description' => 'Fix invoice layout and display remaining amount on invoice'],
            ['title' => 'Client area: OCR CIN / Payment tracking / Calculations', 'description' => 'CIN OCR scanning, payment follow-up, and calculation features for client area'],
            ['title' => 'Gift product stock validation', 'description' => 'Prevent gifting products with insufficient quantity when already added as paid in the same order'],
            ['title' => 'Pending order stock conflict alert', 'description' => 'Alert user when adding a product that exists in a pending order with insufficient stock'],
            ['title' => 'Attach cheque to pending orders', 'description' => 'Allow cheque attachment to orders even when order status is pending'],
            ['title' => 'CIN field on edit form', 'description' => 'Add CIN input field to customer/client edit page'],
            ['title' => 'Cheque photo upload', 'description' => 'Add ability to upload and store cheque photos'],
            ['title' => 'Search by phone / CIN', 'description' => 'Add search functionality by phone number and CIN across the application'],
            ['title' => 'Order advance payment for installments', 'description' => 'Add advance payment option during order creation to support installment payments'],
            ['title' => 'Orders list: customer/client switch + date format', 'description' => 'Add toggle to filter customers vs clients in orders list and change date format to dd/mm/yyyy'],
            ['title' => 'User permissions for customers/clients visibility', 'description' => 'Add role-based permissions to restrict user access to customers only, clients only, or both'],
            ['title' => 'Mark as paid date picker popup', 'description' => 'Show date picker popup when clicking mark as paid to select the actual payment date'],
            ['title' => 'Filter clients by late installments', 'description' => 'Add filter to find clients who have overdue installment payments'],
            ['title' => 'Order type drill-down with date filter', 'description' => 'Show list of orders on click of each sales type in user profile stats with date filtering'],
            ['title' => 'Print installments list for manual verification', 'description' => 'Add printable list of installments for manual payment verification'],
        ];

        foreach ($items as $item) {
            ProgressItem::create([
                'title' => $item['title'],
                'description' => $item['description'],
                'price' => 1500.00,
                'status' => 'not_started',
                'payment_status' => 'unpaid',
                'amount_paid' => 0,
                'is_visible' => true,
            ]);
        }

        $this->info('Successfully created '.count($items).' progress items at 1,500 MAD each.');
        $this->info('Total: '.number_format(count($items) * 1500, 2).' MAD');

        return Command::SUCCESS;
    }
}
