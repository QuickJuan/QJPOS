<?php
namespace App\Filament\Tenant\Resources\OrdersReport\DailySalesReport;

use App\Enums\Order\Status;
use App\Filament\Tenant\Exports\DailySalesReport\PerItemEmailExporter;
use App\Filament\Tenant\Exports\DailySalesReport\PerItemExporter;
use App\Filament\Tenant\Resources\OrdersReport\DailySalesReport\PerItemResource\Pages;
use App\Jobs\OrdersReport\DailySalesReportPerItemJob;
use App\Models\Brand;
use App\Models\Category;
use App\Models\OrdersReport\DailySalesReportPerItem;
use Carbon\Carbon;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Columns\Column;

class PerItemResource extends Resource
{
    protected static ?string $model           = DailySalesReportPerItem::class;
    protected static ?string $navigationGroup = "Order Reports";
    protected static ?string $navigationLabel = "Daily Sales Report - Per Item";
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

                TextColumn::make('item_name')
                    ->label('Item Name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('quantity')
                    ->sortable(),

                TextColumn::make('price')
                    ->money('php')
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
            ])
            ->filters([
                DateRangeFilter::make('order_date'),

                SelectFilter::make('category_id')
                    ->label('Category')
                    ->options(Category::pluck('name', 'id')),

                SelectFilter::make('brand_id')
                    ->label('Brand')
                    ->options(Brand::pluck('name', 'id')),

                SelectFilter::make('order_status')
                    ->options(Status::filamentOptions()),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exports([
                        PerItemExporter::make()
                            ->withColumns([
                                Column::make('order_date')
                                    ->formatStateUsing(fn($state) => Carbon::parse($state)->format("F d, Y H:i A"))
                                    ->heading('Order Date'),

                                Column::make('item_name')
                                    ->heading('Item Name'),

                                Column::make('quantity')
                                    ->heading('Quantity'),

                                Column::make('price')
                                    ->heading('Price'),

                                Column::make('gross_sales')
                                    ->heading('Gross Sales'),

                                Column::make('discount')
                                    ->heading('Discount'),

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

                        $exporter = new PerItemEmailExporter($query);

                        $fileName = 'daily_sales_report_per_item.xlsx';
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

                        dispatch(new DailySalesReportPerItemJob(
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
            'index'  => Pages\ListPerItems::route('/'),
            'create' => Pages\CreatePerItem::route('/create'),
            'edit'   => Pages\EditPerItem::route('/{record}/edit'),
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
