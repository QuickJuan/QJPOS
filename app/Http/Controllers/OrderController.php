<?php
namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Services\OrderService;
use App\Settings\GeneralSettings;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\RefundRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\GeneralSettingsService;
use App\Services\OtpSecretService;
use App\Services\InventoryStockService;
use App\Http\Resources\ReceiptOrdersResource;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {
    }

    public function index(Request $request)
    {
        $generalSettings = app(GeneralSettingsService::class)->getCompanySettings();
        $orders = $this->orderService->getOrders(
            filters: $request->only(['search', 'date_from', 'date_to', 'status', 'cashier_id']),
            perPage: 10
        );

        $cashiers = User::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Transactions/Index', [
            'orders'   => ReceiptOrdersResource::collection($orders),
            'cashiers' => $cashiers,
            'filters'  => $request->only(['search', 'date_from', 'date_to', 'status', 'cashier_id']),
            'generalSettings' => $generalSettings,
        ]);
    }


    public function refund(RefundRequest $request, Order $order)
    {
        if ($order->status !== 'settled') {
            return redirect()->back()->withErrors(['message' => 'Only settled orders can be refunded']);
        }

        // Get the supervisor/approver user by ID
        $supervisor = User::where('id', $request->supervisor_id)
            ->where('branch_id', auth()->user()->branch_id)
            ->first();

        if (!$supervisor) {
            return redirect()->back()->withErrors(['message' => 'Supervisor not found in your branch.']);
        }

        // Verify supervisor has OTP enabled
        if (!$supervisor->otp_enabled || !$supervisor->otp_secret) {
            return redirect()->back()->withErrors(['message' => 'Selected supervisor does not have OTP enabled.']);
        }

        // Verify OTP code using supervisor's secret
        if (!OtpSecretService::verifyCode($supervisor->otp_secret, $request->otp_code)) {
            return redirect()->back()->withErrors(['message' => 'Invalid OTP code. Please try again.']);
        }

        try {
            DB::transaction(function () use ($order, $request, $supervisor) {
                // Revert inventory that was deducted during order settlement
                app(InventoryStockService::class)->restoreOrderInventory($order);

                // Update order with refund status and metadata
                $order->update([
                    'status'    => 'refund',
                    'refunded_cashier_id' => $request->user()->id,
                    'refunded_at' => now(),
                    'meta_data' => array_merge($order->meta_data ?? [], [
                        'refund' => [
                            'requested_by' => $request->user()->name,
                            'supervisor'   => $supervisor->name,
                            'notes'        => $request->notes,
                            'refunded_at'  => now(),
                        ],
                    ])
                ]);
            });

            return redirect()->back()->with('success', 'Order refunded successfully');
        } catch (\Exception $e) {
            \Log::error('Refund failed: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'user_id' => $request->user()->id,
                'exception' => $e,
            ]);
            return redirect()->back()->withErrors(['message' => 'Refund failed: ' . $e->getMessage()]);
        }
    }

    public function getApprovers(): JsonResponse
    {
        try {
            // Get eligible approvers (admin, manager, supervisor, OIC)
            $eligibleRoles = ['admin', 'manager', 'supervisor', 'oic'];
            $approvers = User::whereHas('roles', function ($query) use ($eligibleRoles) {
                $query->whereIn('name', $eligibleRoles);
            })
                ->where('branch_id', auth()->user()->branch_id)
                ->where('otp_enabled', true)
                ->select('id', 'name', 'email')
                ->get();

            return response()->json($approvers);
        } catch (\Exception $e) {
            \Log::error('Get approvers error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'There was an error retrieving approvers.',
            ], 500);
        }
    }

    public function sendReceiptEmail(Request $request, Order $order): JsonResponse
    {
        try {
            $validated = $request->validate([
                'emails' => ['required', 'array', 'min:1'],
                'emails.*' => ['required', 'email'],
                'exportAsPdf' => ['boolean'],
            ]);

            // Queue email sending job with receipt image attachment
            \Mail::to($validated['emails'])
                ->queue(new \App\Mail\ReceiptMail($order));

            return response()->json([
                'success' => true,
                'message' => 'Receipt email queued successfully.',
                'recipients' => count($validated['emails']),
            ]);
        } catch (\Exception $e) {
            \Log::error('Send receipt email error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'There was an error sending the email.',
            ], 500);
        }
    }

    public function downloadReceipt(Order $order)
    {
        try {
            // Load relationships needed for the receipt
            $order->load([
                'orderItems',
                'branch',
                'cashier',
                'payments.paymentMethod',
                'payments.currency'
            ]);

            // Get general settings for company name
            $generalSettings = app(GeneralSettingsService::class)->getCompanySettings();

            info($generalSettings);

            // Transform order data using ReceiptOrdersResource to get properly formatted payment data
            $orderResource = new ReceiptOrdersResource($order);
            $orderData = $orderResource->toArray(request());

            // Render the receipt blade template as HTML
            $html = view('receipt.receipt', [
                'order' => $order,
                'companyName' => $generalSettings->company_name ?? '',
                'branch' => $order->branch,
                'paymentData' => $orderData['payment'], // Pass formatted payment data
            ])->render();

            // Return as downloadable HTML file
            return response($html)
                ->header('Content-Type', 'text/html; charset=utf-8')
                ->header('Content-Disposition', "attachment; filename=\"Receipt-{$order->invoice_no}.html\"");
        } catch (\Exception $e) {
            \Log::error('Download receipt error: ' . $e->getMessage());
            abort(500, 'Failed to download receipt');
        }
    }
}

