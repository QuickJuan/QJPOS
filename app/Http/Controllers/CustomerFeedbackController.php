<?php

namespace App\Http\Controllers;

use App\Enums\CustomerFeedbackStatus;
use App\Models\CustomerFeedback;
use App\Models\Order;
use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerFeedbackController extends Controller
{
    public function create(int $invoiceNo): View
    {
        $order = Order::query()
            ->where('invoice_no', $invoiceNo)
            ->firstOrFail();

        if ($order->customerFeedback()->exists()) {
            return view('customer-feedback.already-submitted', [
                'order' => $order,
            ]);
        }

        return view('customer-feedback.form', [
            'order' => $order,
        ]);
    }

    public function store(Request $request, int $invoiceNo): RedirectResponse
    {
        $order = Order::query()
            ->where('invoice_no', $invoiceNo)
            ->firstOrFail();

        if ($order->customerFeedback()->exists()) {
            return redirect()
                ->route('customer-feedback.already-submitted', ['invoiceNo' => $invoiceNo]);
        }

        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'message' => ['required', 'string', 'max:5000'],
            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => ['file', 'image', 'max:5120'],
        ]);

        $feedback = CustomerFeedback::create([
            'order_id' => $order->id,
            'invoice_no' => (int) $order->invoice_no,
            'name' => $data['name'] ?? null,
            'email' => $data['email'] ?? null,
            'rating' => $data['rating'],
            'message' => $data['message'],
            'status' => CustomerFeedbackStatus::Pending->value,
            'meta' => [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ],
        ]);

        foreach ($request->file('photos', []) as $photo) {
            if (! $photo) {
                continue;
            }

            $feedback
                ->addMedia($photo)
                ->toMediaCollection('photos');
        }

        $this->notifyAdmins($feedback);

        return redirect('/')
            ->with('success', 'Thank you! Your feedback has been submitted.');
    }

    public function thankYou(int $invoiceNo): View
    {
        $order = Order::query()
            ->where('invoice_no', $invoiceNo)
            ->firstOrFail();

        return view('customer-feedback.thank-you', [
            'order' => $order,
        ]);
    }

    public function alreadySubmitted(int $invoiceNo): View
    {
        $order = Order::query()
            ->where('invoice_no', $invoiceNo)
            ->firstOrFail();

        return view('customer-feedback.already-submitted', [
            'order' => $order,
        ]);
    }

    private function notifyAdmins(CustomerFeedback $feedback): void
    {
        $users = User::role(['Admin', 'Manager'])->get();
        $viewUrl = '/admin/customer-feedbacks/' . $feedback->id;

        foreach ($users as $user) {
            Notification::make()
                ->title('New Customer Feedback')
                ->body('Invoice #' . $feedback->invoice_no . ' submitted feedback')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->iconColor('warning')
                ->actions([
                    Action::make('view')
                        ->label('View Feedback')
                        ->url($viewUrl),
                ])
                ->sendToDatabase($user);
        }
    }
}
