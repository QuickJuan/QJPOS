<?php
namespace App\Jobs\OrdersReport;

use App\Mail\OrdersReport\DailySalesReportPerItemMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class DailySalesReportPerItemJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(protected array $emails, protected array $ccEmails, protected string $filePath)
    {
        $this->emails   = $emails;
        $this->ccEmails = $ccEmails;
        $this->filePath = $filePath;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $mail = Mail::to($this->emails);

        if (! empty($this->ccEmails)) {
            $mail->cc($this->ccEmails);
        }

        $mail->send(new DailySalesReportPerItemMail($this->filePath));
    }
}
