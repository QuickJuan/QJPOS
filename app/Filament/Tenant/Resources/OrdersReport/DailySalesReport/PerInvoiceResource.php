<?php
namespace App\Filament\Tenant\Resources\OrdersReport\DailySalesReport;

use Carbon\Carbon;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Enums\Order\Status;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use pxlrbt\FilamentExcel\Columns\Column;
use Filament\Tables\Filters\SelectFilter;
use App\Filament\Tenant\Exports\PerInvoiceExporter;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use App\Models\OrdersReport\DailySalesReportPerInvoice;
use App\Filament\Tenant\Exports\PerInvoiceEmailExporter;
use App\Jobs\OrdersReport\DailySalesReportPerInvoiceJob;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use App\Filament\Tenant\Resources\OrdersReport\DailySalesReport\PerInvoiceResource\Pages;

class PerInvoiceResource extends Resource
{
    protected static ?string $model           = DailySalesReportPerInvoice::class;
    protected static ?string $navigationGroup = "Order Reports";
    protected static ?string $navigationLabel = "Daily Sales Report - Per Invoice";
    protected static ?string $navigationIcon  = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_date')
                    ->label('Order Date')
                    ->sortable()
                    ->date('F d, Y H:i a'),

                TextColumn::make('cashier_shift_number')
                    ->label('Cashier Shift Number')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('customer')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('invoice_number')
                    ->label('Invoice Number')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('gross_sales')
                    ->label('Gross Sales')
                    ->money('php')
                    ->sortable(),

                TextColumn::make('discount')
                    ->money('php')
                    ->sortable(),

                TextColumn::make('net_sales')
                    ->label('Net Sales')
                    ->money('php')
                    ->sortable(),

                TextColumn::make('status')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                DateRangeFilter::make('order_date'),

                SelectFilter::make('status')
                    ->options(Status::filamentOptions())
            ])
            ->headerActions([
                ExportAction::make()
                    ->exports([
                        PerInvoiceExporter::make()
                            ->withColumns([
                                Column::make('order_date')
                                    ->formatStateUsing(fn($state) => Carbon::parse($state)->format("F d, Y H:i A"))
                                    ->heading('Order Date'),

                                Column::make('cashier_shift_number')
                                    ->heading('Cashier Shift Number'),

                                Column::make('customer'),

                                Column::make('invoice_number')
                                    ->heading('Invoice Number'),

                                Column::make('gross_sales')
                                    ->heading('Gross Sales'),

                                Column::make('discount'),

                                Column::make('net_sales')
                                    ->heading('Net Sales'),

                                Column::make('status')
                                    ->heading('Status'),
                            ]),
                    ]),

                Action::make('send_to_email')
                    ->label('Send to Email')
                    ->icon('heroicon-o-envelope')
                    ->form([
                        TextInput::make('emails')
                            ->label('Emails')
                            ->required()
                            ->placeholder('Enter email addresses (comma separated)'),

                        TextInput::make('cc_emails')
                            ->label('CC Emails')
                            ->placeholder('Enter CC email addresses (comma separated)'),
                    ])
                    ->action(function (array $data, Table $table) {
                        $query = $table->getQuery();

                        $exporter = new PerInvoiceEmailExporter($query);

                        $fileName = 'daily_sales_report_per_invoice.xlsx';
                        $path     = 'exports/' . $fileName;

                        if (! file_exists(Storage::disk('public')->path($path))) {
                            throw new \Exception("File not found: $path");
                        }

                        Excel::store($exporter, $path, 'public');

                        $emailsRaw = $data['emails'] ?? '';
                        $ccRaw     = $data['cc_emails'] ?? '';

                        $emails   = array_values(array_filter(array_map('trim', explode(',', $emailsRaw))));
                        $ccEmails = array_values(array_filter(array_map('trim', explode(',', $ccRaw))));

                        // validate each email address
                        foreach ($emails as $e) {
                            if (! filter_var($e, FILTER_VALIDATE_EMAIL)) {
                                Notification::make()
                                    ->title('Invalid email')
                                    ->body("Invalid email address: $e")
                                    ->danger()
                                    ->send();

                                return;
                            }
                        }

                        foreach ($ccEmails as $e) {
                            if (! filter_var($e, FILTER_VALIDATE_EMAIL)) {
                                Notification::make()
                                    ->title('Invalid CC email')
                                    ->body("Invalid CC email address: $e")
                                    ->danger()
                                    ->send();

                                return;
                            }
                        }

                        dispatch(new DailySalesReportPerInvoiceJob(
                            $emails,
                            $ccEmails,
                            // pass the local filesystem path so the mailer can attach the file
                            Storage::disk('public')->path($path)
                        ));

                        Notification::make()
                            ->title('Email sent successfully')
                            ->body('The report has been sent to the specified emails.')
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPerInvoices::route('/'),
            'create' => Pages\CreatePerInvoice::route('/create'),
            'edit'   => Pages\EditPerInvoice::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }
}
