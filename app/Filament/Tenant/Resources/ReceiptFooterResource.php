<?php
namespace App\Filament\Tenant\Resources;

use App\Enums\Receipt\Type;
use App\Filament\Tenant\Resources\ReceiptFooterResource\Pages;
use App\Models\ReceiptFooter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReceiptFooterResource extends Resource
{
    protected static ?string $model = ReceiptFooter::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Textarea::make('footer_notes')
                    ->required(),

                Select::make('type')
                    ->options(Type::filamentOptions())
                    ->required()
                    ->default(Type::RECEIPT->value),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('footer_notes')
                    ->words(5),

                TextColumn::make('type')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
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
            'index'  => Pages\ListReceiptFooters::route('/'),
            'create' => Pages\CreateReceiptFooter::route('/create'),
            'edit'   => Pages\EditReceiptFooter::route('/{record}/edit'),
        ];
    }
}
