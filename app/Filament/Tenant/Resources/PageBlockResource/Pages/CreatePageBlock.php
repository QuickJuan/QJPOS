<?php

namespace App\Filament\Tenant\Resources\PageBlockResource\Pages;

use App\Filament\Tenant\Resources\PageBlockResource;
use App\Filament\Forms\BlockFormBuilder;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;

class CreatePageBlock extends CreateRecord
{
    protected static string $resource = PageBlockResource::class;

    public function form(Form $form): Form
    {
        $blockTypeSlug = $this->data['block_type_id'] ?? null;
        $blockType = $blockTypeSlug ? \App\Models\PageBlockType::find($blockTypeSlug)?->slug : null;

        return $form
            ->schema([
                Section::make('Block Configuration')
                    ->description('Create a new page block')
                    ->schema([
                        Select::make('page_id')
                            ->relationship('page', 'title')
                            ->required()
                            ->searchable()
                            ->helperText('Select the page to add this block to'),

                        Select::make('block_type_id')
                            ->label('Block Type')
                            ->relationship('blockType', 'name')
                            ->required()
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function () {
                                $this->form->fill($this->data);
                            })
                            ->helperText('Select the type of block to create'),

                        TextInput::make('order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Display order (lower numbers appear first)'),
                    ])->columns(2),

                ...$blockType ? BlockFormBuilder::getFormSchema($blockType) : [],
            ]);
    }
}
