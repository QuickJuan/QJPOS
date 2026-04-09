<?php

namespace App\Filament\Tenant\Resources;

use App\Enums\CustomerFeedbackStatus;
use App\Filament\Tenant\Resources\CustomerFeedbackResource\Pages;
use App\Models\CustomerFeedback;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CustomerFeedbackResource extends Resource
{
    protected static ?string $model = CustomerFeedback::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationLabel = 'Customer Feedback';
    protected static ?string $navigationGroup = 'Customer Management';
    protected static ?int $navigationSort = 99;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Feedback')
                ->schema([
                    TextInput::make('invoice_no')
                        ->label('Invoice #')
                        ->disabled()
                        ->dehydrated(false),

                    TextInput::make('name')
                        ->maxLength(255)
                        ->nullable(),

                    TextInput::make('email')
                        ->email()
                        ->maxLength(255)
                        ->nullable(),

                    Select::make('rating')
                        ->options([
                            5 => '5 stars',
                            4 => '4 stars',
                            3 => '3 stars',
                            2 => '2 stars',
                            1 => '1 star',
                        ])
                        ->required(),

                    Textarea::make('message')
                        ->rows(6)
                        ->required()
                        ->maxLength(5000)
                        ->columnSpanFull(),

                    ToggleButtons::make('status')
                        ->options(CustomerFeedbackStatus::options())
                        ->inline()
                        ->required()
                        ->default(CustomerFeedbackStatus::Pending->value),

                    SpatieMediaLibraryFileUpload::make('photos')
                        ->collection('photos')
                        ->multiple()
                        ->reorderable()
                        ->image()
                        ->maxFiles(5)
                        ->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_no')
                    ->label('Invoice #')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('name')
                    ->label('Name')
                    ->default('Anonymous')
                    ->searchable(),

                TextColumn::make('rating')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(function ($state): string {
                        if ($state instanceof CustomerFeedbackStatus) {
                            return $state->label();
                        }

                        return CustomerFeedbackStatus::from((string) $state)->label();
                    })
                    ->color(function ($state): string {
                        if ($state instanceof CustomerFeedbackStatus) {
                            return $state->color();
                        }

                        return CustomerFeedbackStatus::from((string) $state)->color();
                    })
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(CustomerFeedbackStatus::options()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomerFeedbacks::route('/'),
            'view' => Pages\ViewCustomerFeedback::route('/{record}'),
            'edit' => Pages\EditCustomerFeedback::route('/{record}/edit'),
        ];
    }
}

