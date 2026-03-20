<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\CareerResource\Pages;
use App\Filament\Tenant\Resources\CareerResource\RelationManagers;
use App\Models\Career;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;

class CareerResource extends Resource
{
    protected static ?string $model = Career::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?string $navigationGroup = 'Human Resource';
    protected static ?string $navigationLabel = 'Careers';
    protected static ?int $navigationSort = 30;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Job Details')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Job Title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('department')
                            ->label('Department')
                            ->maxLength(100),

                        Forms\Components\TextInput::make('location')
                            ->label('Location')
                            ->placeholder('e.g. Manila, Philippines / Remote')
                            ->maxLength(150),

                        Forms\Components\Select::make('employment_type')
                            ->label('Employment Type')
                            ->options([
                                'full-time'  => 'Full-time',
                                'part-time'  => 'Part-time',
                                'contract'   => 'Contract',
                                'internship' => 'Internship',
                            ])
                            ->default('full-time')
                            ->required(),

                        Forms\Components\TextInput::make('salary_range')
                            ->label('Salary Range')
                            ->placeholder('e.g. ₱25,000 – ₱35,000 / month')
                            ->maxLength(100),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft'     => 'Draft',
                                'available' => 'Available',
                                'closed'    => 'Closed',
                            ])
                            ->default('draft')
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Content')
                    ->schema([
                        Forms\Components\Textarea::make('summary')
                            ->label('Short Summary')
                            ->rows(3)
                            ->helperText('Displayed on the career card (2-3 sentences)')
                            ->columnSpanFull(),

                        Forms\Components\RichEditor::make('description')
                            ->label('Full Job Description')
                            ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList', 'h2', 'h3', 'link', 'redo', 'undo'])
                            ->columnSpanFull(),

                        Forms\Components\RichEditor::make('responsibilities')
                            ->label('Responsibilities')
                            ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList', 'redo', 'undo'])
                            ->columnSpanFull(),

                        Forms\Components\RichEditor::make('requirements')
                            ->label('Requirements / Qualifications')
                            ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList', 'redo', 'undo'])
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Job Title')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('department')
                    ->label('Department')
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('location')
                    ->label('Location')
                    ->placeholder('—'),

                TextColumn::make('employment_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'full-time'  => 'success',
                        'part-time'  => 'info',
                        'contract'   => 'warning',
                        'internship' => 'gray',
                        default      => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'available' => 'success',
                        'closed'    => 'danger',
                        'draft'     => 'gray',
                        default     => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('applications_count')
                    ->label('Applications')
                    ->counts('applications')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft'     => 'Draft',
                        'available' => 'Available',
                        'closed'    => 'Closed',
                    ]),
                SelectFilter::make('employment_type')
                    ->label('Type')
                    ->options([
                        'full-time'  => 'Full-time',
                        'part-time'  => 'Part-time',
                        'contract'   => 'Contract',
                        'internship' => 'Internship',
                    ]),
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
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ApplicationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCareers::route('/'),
            'create' => Pages\CreateCareer::route('/create'),
            'view'   => Pages\ViewCareer::route('/{record}'),
            'edit'   => Pages\EditCareer::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([
            \Illuminate\Database\Eloquent\SoftDeletingScope::class,
        ]);
    }
}
