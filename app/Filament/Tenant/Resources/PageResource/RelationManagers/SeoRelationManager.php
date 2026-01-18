<?php

namespace App\Filament\Tenant\Resources\PageResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SeoRelationManager extends RelationManager
{
    protected static string $relationship = 'seo';

    protected static ?string $title = 'SEO Settings';

    protected static ?string $recordTitleAttribute = 'meta_title';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Search Engine Optimization')
                    ->description('Configure SEO metadata for search engines and social media')
                    ->schema([
                        Forms\Components\TextInput::make('meta_title')
                            ->label('SEO Title')
                            ->maxLength(60)
                            ->placeholder('Enter SEO title (optimal: 50-60 characters)')
                            ->helperText('If empty, page title will be used for SEO'),

                        Forms\Components\Textarea::make('meta_description')
                            ->label('SEO Meta Description')
                            ->maxLength(160)
                            ->placeholder('Enter meta description (optimal: 150-160 characters)')
                            ->helperText('Brief description for search engines and social media')
                            ->rows(3),

                        Forms\Components\Textarea::make('meta_keywords')
                            ->label('SEO Keywords')
                            ->placeholder('keyword1, keyword2, keyword3')
                            ->helperText('Comma-separated keywords for SEO')
                            ->rows(2),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('meta_title')
            ->columns([
                Tables\Columns\TextColumn::make('meta_title')
                    ->label('SEO Title')
                    ->placeholder('Not set'),
                Tables\Columns\TextColumn::make('meta_description')
                    ->label('Description')
                    ->limit(50)
                    ->placeholder('Not set'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add SEO Settings'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->emptyStateHeading('No SEO settings configured')
            ->emptyStateDescription('Add SEO metadata to improve search engine visibility')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add SEO Settings'),
            ]);
    }
}
