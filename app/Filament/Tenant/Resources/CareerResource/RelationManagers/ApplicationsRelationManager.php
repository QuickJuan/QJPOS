<?php

namespace App\Filament\Tenant\Resources\CareerResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class ApplicationsRelationManager extends RelationManager
{
    protected static string $relationship = 'applications';
    protected static ?string $recordTitleAttribute = 'email';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('status')
                    ->options([
                        'new'         => 'New',
                        'reviewing'   => 'Reviewing',
                        'shortlisted' => 'Shortlisted',
                        'rejected'    => 'Rejected',
                    ])
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('first_name')
                    ->label('Name')
                    ->formatStateUsing(fn ($record) => $record->first_name . ' ' . $record->last_name)
                    ->searchable(['first_name', 'last_name']),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                TextColumn::make('phone')
                    ->label('Phone'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'new'         => 'info',
                        'reviewing'   => 'warning',
                        'shortlisted' => 'success',
                        'rejected'    => 'danger',
                        default       => 'gray',
                    }),

                TextColumn::make('resume_path')
                    ->label('Resume')
                    ->formatStateUsing(fn ($state) => $state ? 'Download' : '—')
                    ->url(fn ($record) => $record->resume_path ? "/storage/{$record->resume_path}" : null)
                    ->openUrlInNewTab()
                    ->color('primary'),

                TextColumn::make('created_at')
                    ->label('Applied')
                    ->dateTime('M j, Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make()->label('Update Status'),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
