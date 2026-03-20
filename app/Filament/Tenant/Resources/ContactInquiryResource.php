<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\ContactInquiryResource\Pages;
use App\Models\ContactInquiry;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;

class ContactInquiryResource extends Resource
{
    protected static ?string $model = ContactInquiry::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $navigationGroup = 'Customer Management';
    protected static ?string $navigationLabel = 'Contact Inquiries';
    protected static ?int $navigationSort = 8;

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('status')
                    ->options([
                        'open'   => 'Open',
                        'closed' => 'Closed',
                    ])
                    ->default('open')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Placeholder::make('created_at')
                    ->label('Received')
                    ->content(fn ($record) => $record?->created_at?->format('F j, Y g:i A'))
                    ->columnSpanFull(),
                Forms\Components\KeyValue::make('fields')
                    ->disabled()
                    ->columnSpanFull(),
                Forms\Components\KeyValue::make('meta')
                    ->disabled()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Name')->sortable()->searchable(),
                TextColumn::make('email')->label('Email')->sortable()->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'open'   => 'warning',
                        'closed' => 'success',
                        default  => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('created_at')->label('Received')->dateTime('M j, Y g:i A')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'open'   => 'Open',
                        'closed' => 'Closed',
                    ]),
                Filter::make('recent')->label('Last 7 days')->query(fn ($query) => $query->where('created_at', '>=', now()->subDays(7))),
                TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContactInquiries::route('/'),
            'view'  => Pages\ViewContactInquiry::route('/{record}'),
            'edit'  => Pages\EditContactInquiry::route('/{record}/edit'),
        ];
    }
}
