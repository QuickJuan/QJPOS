<?php
namespace App\Filament\Tenant\Resources\OrdersReport\BestSellerReportResource;

use App\Filament\Tenant\Exports\BestSellerReport\BestSellerReportEmailExporter;
use App\Filament\Tenant\Exports\BestSellerReport\BestSellerReportExporter;
use App\Filament\Tenant\Resources\OrdersReport\BestSellerReportResource\Pages\CreateBestSellerReport;
use App\Filament\Tenant\Resources\OrdersReport\BestSellerReportResource\Pages\EditBestSellerReport;
use App\Filament\Tenant\Resources\OrdersReport\BestSellerReportResource\Pages\ListBestSellerReports;
use App\Jobs\OrdersReport\BestSellerReportJob;
use App\Models\OrdersReport\BestSellerReport;
use Carbon\Carbon;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Columns\Column;

class BestSellerReportResource extends Resource
{
    protected static ?string $model           = BestSellerReport::class;
    protected static ?string $navigationGroup = "Order Reports";
    protected static ?string $navigationLabel = "Best Seller Report";
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
            ->defaultSort('qty', 'desc')
            ->columns([
                TextColumn::make('order_date')
                    ->label('Order Date')
                    ->sortable()
                    ->date('F d, Y h:i A'),

                TextColumn::make('product')
                    ->label('Product')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('qty')
                    ->label('Quantity')
                    ->numeric(0)
                    ->sortable(),

                TextColumn::make('net_sales')
                    ->label('Net Sales')
                    ->money('php')
                    ->sortable(),
            ])
            ->filters([
                DateRangeFilter::make('order_date'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exports([
                        BestSellerReportExporter::make()
                            ->fromTable()
                            ->withColumns([
                                Column::make('order_date')
                                    ->formatStateUsing(fn($state) => Carbon::parse($state)->format("F d, Y H:i A"))
                                    ->heading('Order Date'),

                                Column::make('product')
                                    ->heading('Product'),

                                Column::make('qty')
                                    ->heading('Quantity'),

                                Column::make('net_sales')
                                    ->heading('Net Sales'),
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
                        $query    = $table->getQuery();
                        $exporter = new BestSellerReportEmailExporter($query);

                        $fileName = 'best_seller_report.xlsx';
                        $path     = "exports/{$fileName}";

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

                        dispatch(new BestSellerReportJob(
                            $emails,
                            $ccEmails,
                            Storage::disk('public')->path($path)
                        ));

                        Notification::make()
                            ->title('Email sent successfully')
                            ->body('The report has been sent to the specified emails.')
                            ->success()
                            ->send();
                    }),
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
            'index'  => ListBestSellerReports::route('/'),
            'create' => CreateBestSellerReport::route('/create'),
            'edit'   => EditBestSellerReport::route('/{record}/edit'),
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
