<?php

namespace App\Console\Commands;

use App\Services\ReceiptImageService;
use Illuminate\Console\Command;

class CleanupOldReceipts extends Command
{
    protected $signature = 'receipts:cleanup';
    protected $description = 'Clean up old receipt HTML files older than 7 days';

    public function handle(ReceiptImageService $receiptService): int
    {
        $this->info('Cleaning up old receipt files...');

        $receiptService->cleanupOldReceipts();

        $this->info('Cleanup completed successfully!');

        return Command::SUCCESS;
    }
}
