<?php

namespace App\Filament\Tenant\Resources\PageResource\RelationManagers;

use App\Filament\Forms\BlockFormBuilder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BlocksRelationManager extends RelationManager
{
    protected static string $relationship = 'blocks';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Block Details')
                    ->schema([
                        Select::make('block_type_id')
                            ->label('Block Type')
                            ->relationship('blockType', 'name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->name)
                            ->required()
                            ->disabled(fn ($record) => $record !== null)
                            ->live(onBlur: false)
                            ->helperText('Cannot be changed after creation'),

                        TextInput::make('order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Display order (lower first)'),
                    ])->columns(2),

                // Banner Block Content
                Section::make('Banner Content')
                    ->hidden(fn ($get, $record) => $record === null || !$this->isBlockType($get('block_type_id'), 'banner'))
                    ->schema(BlockFormBuilder::getFormSchema('banner')),

                // Text Block Content
                Section::make('Text Content')
                    ->hidden(fn ($get, $record) => $record === null || !$this->isBlockType($get('block_type_id'), 'text'))
                    ->schema(BlockFormBuilder::getFormSchema('text')),

                // Gallery Block Content
                Section::make('Gallery Content')
                    ->hidden(fn ($get, $record) => $record === null || !$this->isBlockType($get('block_type_id'), 'gallery'))
                    ->schema(BlockFormBuilder::getFormSchema('gallery')),

                // Products Block Content
                Section::make('Products Content')
                    ->hidden(fn ($get, $record) => $record === null || !$this->isBlockType($get('block_type_id'), 'products'))
                    ->schema(BlockFormBuilder::getFormSchema('products')),

                // Reviews Block Content
                Section::make('Reviews Content')
                    ->hidden(fn ($get, $record) => $record === null || !$this->isBlockType($get('block_type_id'), 'reviews'))
                    ->schema(BlockFormBuilder::getFormSchema('reviews')),

                // Testimonial Block Content
                Section::make('Testimonial Content')
                    ->hidden(fn ($get, $record) => $record === null || !$this->isBlockType($get('block_type_id'), 'testimonial'))
                    ->schema(BlockFormBuilder::getFormSchema('testimonial')),

                // CTA Block Content
                Section::make('CTA Content')
                    ->hidden(fn ($get, $record) => $record === null || !$this->isBlockType($get('block_type_id'), 'cta'))
                    ->schema(BlockFormBuilder::getFormSchema('cta')),

                // FAQ Block Content
                Section::make('FAQ Content')
                    ->hidden(fn ($get, $record) => $record === null || !$this->isBlockType($get('block_type_id'), 'faq'))
                    ->schema(BlockFormBuilder::getFormSchema('faq')),

                // Stats Block Content
                Section::make('Stats Content')
                    ->hidden(fn ($get, $record) => $record === null || !$this->isBlockType($get('block_type_id'), 'stats'))
                    ->schema(BlockFormBuilder::getFormSchema('stats')),

                // Newsletter Block Content
                Section::make('Newsletter Content')
                    ->hidden(fn ($get, $record) => $record === null || !$this->isBlockType($get('block_type_id'), 'newsletter'))
                    ->schema(BlockFormBuilder::getFormSchema('newsletter')),

                // Contact Form Block Content
                Section::make('Contact Form Content')
                    ->hidden(fn ($get, $record) => $record === null || !$this->isBlockType($get('block_type_id'), 'contact-form'))
                    ->schema(BlockFormBuilder::getFormSchema('contact-form')),

                // Product List Block Content
                Section::make('Product List Content')
                    ->hidden(fn ($get, $record) => $record === null || !$this->isBlockType($get('block_type_id'), 'product-list'))
                    ->schema(BlockFormBuilder::getFormSchema('product-list')),

                // Features Block Content
                Section::make('Features Content')
                    ->hidden(fn ($get, $record) => $record === null || !$this->isBlockType($get('block_type_id'), 'features'))
                    ->schema(BlockFormBuilder::getFormSchema('features')),

                // Careers Block Content
                Section::make('Careers Block')
                    ->hidden(fn ($get, $record) => $record === null || !$this->isBlockType($get('block_type_id'), 'careers'))
                    ->schema(BlockFormBuilder::getFormSchema('careers')),

                // Blog Block Content
                Section::make('Blog Block')
                    ->hidden(fn ($get, $record) => $record === null || !$this->isBlockType($get('block_type_id'), 'articles'))
                    ->schema(BlockFormBuilder::getFormSchema('articles')),

                // Accordion Block Content
                Section::make('Accordion Block')
                    ->hidden(fn ($get, $record) => $record === null || !$this->isBlockType($get('block_type_id'), 'accordion'))
                    ->schema(BlockFormBuilder::getFormSchema('accordion')),
            ]);
    }

    private function isBlockType($blockTypeId, $slug): bool
    {
        if (!$blockTypeId) {
            return false;
        }

        $blockType = \App\Models\PageBlockType::find($blockTypeId);
        return $blockType?->slug === $slug;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('blockType.name')
                    ->label('Type')
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('content.title')
                    ->label('Title')
                    ->limit(50)
                    ->searchable(),

                TextColumn::make('order')
                    ->label('Order')
                    ->sortable()
                    ->width('80px'),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('block_type_id')
                    ->relationship('blockType', 'name'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        // Auto-increment order based on existing blocks
                        $maxOrder = $this->getOwnerRecord()->blocks()->max('order') ?? -1;
                        $data['order'] = $maxOrder + 1;
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->reorderable('order');
    }
}
