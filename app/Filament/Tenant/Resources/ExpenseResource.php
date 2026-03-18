<?php

namespace App\Filament\Tenant\Resources;

use App\Enums\ExpensePaymentMethod;
use App\Enums\ExpenseStatus;
use App\Filament\Tenant\Resources\ExpenseResource\Pages;
use App\Filament\Tenant\Resources\ExpenseResource\RelationManagers\PaymentsRelationManager;
use App\Models\Expense;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ExpenseResource extends Resource
{
    protected static ?string $model           = Expense::class;
    protected static ?string $navigationIcon  = 'heroicon-o-receipt-percent';
    protected static ?string $navigationLabel = 'Expenses';
    protected static ?string $navigationGroup = 'Finance';
    protected static ?int $navigationSort     = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([

            // ── Expense Details ────────────────────────────────────
            Section::make('Expense Details')
                ->icon('heroicon-o-document-text')
                ->schema([
                    TextInput::make('title')
                        ->label('Expense Title')
                        ->required()
                        ->maxLength(255),

                    Select::make('expense_category_id')
                        ->label('Category')
                        ->relationship('category', 'name')
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            TextInput::make('name')->required()->maxLength(100),
                            \Filament\Forms\Components\ColorPicker::make('color')->label('Color'),
                            Textarea::make('description')->rows(2),
                        ])
                        ->nullable(),

                    TextInput::make('payee')
                        ->label('Payee / Vendor')
                        ->maxLength(255)
                        ->placeholder('Supplier or vendor name'),

                    DatePicker::make('expense_date')
                        ->label('Expense Date')
                        ->required()
                        ->default(today())
                        ->native(false),

                    TextInput::make('amount')
                        ->label('Total Amount')
                        ->required()
                        ->numeric()
                        ->prefix('₱')
                        ->minValue(0.01)
                        ->live()
                        ->step(0.01),

                    Textarea::make('description')
                        ->label('Description')
                        ->rows(3)
                        ->columnSpanFull(),

                    Textarea::make('notes')
                        ->label('Internal Notes')
                        ->rows(2)
                        ->columnSpanFull(),
                ])->columns(2),

            // ── Payment Method ─────────────────────────────────────
            Section::make('Payment Method')
                ->icon('heroicon-o-banknotes')
                ->schema([
                    ToggleButtons::make('payment_method')
                        ->label('How was this paid?')
                        ->options(ExpensePaymentMethod::options())
                        ->icons([
                            'cash'     => 'heroicon-o-banknotes',
                            'bank'     => 'heroicon-o-building-library',
                            'purchase' => 'heroicon-o-credit-card',
                        ])
                        ->colors([
                            'cash'     => 'success',
                            'bank'     => 'info',
                            'purchase' => 'warning',
                        ])
                        ->required()
                        ->default('cash')
                        ->inline()
                        ->live()
                        ->columnSpanFull(),

                    // ── Cash / Bank: single payment details ──────────
                    TextInput::make('reference_number')
                        ->label('Reference / OR Number')
                        ->maxLength(100)
                        ->placeholder('Transaction or receipt reference')
                        ->visible(fn (Get $get) => in_array($get('payment_method'), ['cash', 'bank']))
                        ->dehydrated(false),

                    DatePicker::make('payment_date')
                        ->label('Date Paid')
                        ->native(false)
                        ->default(today())
                        ->visible(fn (Get $get) => in_array($get('payment_method'), ['cash', 'bank']))
                        ->dehydrated(false),

                    Textarea::make('payment_remarks')
                        ->label('Remarks')
                        ->rows(2)
                        ->visible(fn (Get $get) => in_array($get('payment_method'), ['cash', 'bank']))
                        ->dehydrated(false)
                        ->columnSpanFull(),

                    // ── Purchase: installment schedule ────────────────
                    Placeholder::make('purchase_hint')
                        ->label('')
                        ->content('Add the payment schedule below. You can mark each installment as paid later.')
                        ->visible(fn (Get $get) => $get('payment_method') === 'purchase')
                        ->columnSpanFull(),

                    Repeater::make('payment_schedule')
                        ->label('Installment / Payment Schedule')
                        ->schema([
                            DatePicker::make('due_date')
                                ->label('Due Date')
                                ->required()
                                ->native(false)
                                ->default(today()),

                            TextInput::make('amount')
                                ->label('Amount')
                                ->required()
                                ->numeric()
                                ->prefix('₱')
                                ->minValue(0.01)
                                ->step(0.01),

                            Textarea::make('notes')
                                ->label('Remarks')
                                ->rows(1)
                                ->columnSpanFull(),
                        ])
                        ->columns(2)
                        ->defaultItems(0)
                        ->addActionLabel('Add Installment')
                        ->collapsible()
                        ->visible(fn (Get $get) => $get('payment_method') === 'purchase')
                        ->dehydrated(false)
                        ->columnSpanFull(),
                ])->columns(2),

            // ── Attachments ────────────────────────────────────────
            Section::make('Attachments')
                ->icon('heroicon-o-paper-clip')
                ->description('Upload receipts, invoices, or photos of purchased items (JPEG, PNG, WebP, PDF).')
                ->schema([
                    SpatieMediaLibraryFileUpload::make('expense_attachments')
                        ->label('')
                        ->collection('expense_attachments')
                        ->multiple()
                        ->maxFiles(10)
                        ->maxSize(10240)
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'application/pdf'])
                        ->previewable()
                        ->downloadable()
                        ->reorderable()
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('expense_date', 'desc')
            ->columns([
                TextColumn::make('expense_date')
                    ->label('Date')
                    ->date('M d, Y')
                    ->sortable(),

                TextColumn::make('title')
                    ->searchable()
                    ->wrap()
                    ->description(fn (Expense $r) => $r->category?->name),

                TextColumn::make('payee')
                    ->label('Payee')
                    ->placeholder('—')
                    ->searchable(),

                TextColumn::make('amount')
                    ->label('Total')
                    ->money('PHP')
                    ->sortable(),

                TextColumn::make('payment_method')
                    ->label('Method')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof ExpensePaymentMethod ? $state->label() : $state)
                    ->color(fn ($state) => $state instanceof ExpensePaymentMethod ? $state->color() : 'gray'),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof ExpenseStatus ? $state->label() : $state)
                    ->color(fn ($state) => $state instanceof ExpenseStatus ? $state->color() : 'gray')
                    ->icon(fn ($state) => $state instanceof ExpenseStatus ? $state->icon() : null),

                TextColumn::make('payments_count')
                    ->label('Installments')
                    ->counts('payments')
                    ->sortable()
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('payment_method')
                    ->options(ExpensePaymentMethod::options()),

                SelectFilter::make('status')
                    ->options(ExpenseStatus::options()),

                SelectFilter::make('expense_category_id')
                    ->label('Category')
                    ->relationship('category', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            PaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit'   => Pages\EditExpense::route('/{record}/edit'),
            'view'   => Pages\ViewExpense::route('/{record}/view'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['category', 'payments']);
    }
}
