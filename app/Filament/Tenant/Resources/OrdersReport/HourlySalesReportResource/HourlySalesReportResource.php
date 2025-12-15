<?php
namespace App\Filament\Tenant\Resources\OrdersReport\HourlySalesReportResource;

use App\Filament\Tenant\Resources\OrdersReport\HourlySalesReportResource\Pages\CreateHourlySalesReport;
use App\Filament\Tenant\Resources\OrdersReport\HourlySalesReportResource\Pages\EditHourlySalesReport;
use App\Filament\Tenant\Resources\OrdersReport\HourlySalesReportResource\Pages\ListHourlySalesReports;
use App\Models\OrdersReport\HourlySalesReport;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class HourlySalesReportResource extends Resource
{
    protected static ?string $model           = HourlySalesReport::class;
    protected static ?string $navigationGroup = "Order Reports";
    protected static ?string $navigationLabel = "Hourly Sales Report";
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
                    ->date('F d, Y'),

                TextColumn::make('order_time')
                    ->sortable(),

                TextColumn::make('item_name')
                    ->label('Item Name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('quantity')
                    ->sortable(),

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
            'index'  => ListHourlySalesReports::route('/'),
            'create' => CreateHourlySalesReport::route('/create'),
            'edit'   => EditHourlySalesReport::route('/{record}/edit'),
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
