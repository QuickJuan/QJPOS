<?php

namespace App\Filament\Forms;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;

class BlockFormBuilder
{
    /**
     * Get the form schema for a specific block type
     */
    public static function getFormSchema(string $blockTypeSlug): array
    {
        return match ($blockTypeSlug) {
            'banner' => self::getBannerSchema(),
            'text' => self::getTextSchema(),
            'gallery' => self::getGallerySchema(),
            'products' => self::getProductsSchema(),
            'reviews' => self::getReviewsSchema(),
            'testimonial' => self::getTestimonialSchema(),
            'cta' => self::getCtaSchema(),
            'faq' => self::getFaqSchema(),
            'stats' => self::getStatsSchema(),
            'newsletter' => self::getNewsletterSchema(),
            'contact-form' => self::getContactFormSchema(),
            default => self::getDefaultSchema(),
        };
    }

    /**
     * Banner Block: Hero section with title, description, image, and CTA
     */
    private static function getBannerSchema(): array
    {
        return [
            Section::make('Banner Content')
                ->schema([
                    TextInput::make('content.title')
                        ->label('Headline')
                        ->required()
                        ->placeholder('Enter banner headline'),

                    Textarea::make('content.subtitle')
                        ->label('Subtitle')
                        ->placeholder('Optional banner subtitle')
                        ->rows(2),

                    FileUpload::make('content.image')
                        ->label('Background Image')
                        ->image()
                        ->maxSize(5120)
                        ->disk('public')
                        ->directory('blocks/banners'),

                    TextInput::make('content.overlay_opacity')
                        ->label('Overlay Opacity')
                        ->numeric()
                        ->default(0.25)
                        ->minValue(0)
                        ->maxValue(1)
                        ->step(0.1)
                        ->helperText('0 = transparent, 1 = opaque'),

                    TextInput::make('content.button_text')
                        ->label('Button Text')
                        ->placeholder('e.g., Learn More'),

                    TextInput::make('content.button_url')
                        ->label('Button URL')
                        ->url()
                        ->placeholder('https://...'),

                    Select::make('content.button_style')
                        ->label('Button Style')
                        ->options([
                            'primary' => 'Primary',
                            'secondary' => 'Secondary',
                            'outline' => 'Outline',
                        ])
                        ->default('primary'),

                    Select::make('content.alignment')
                        ->label('Text Alignment')
                        ->options([
                            'left' => 'Left',
                            'center' => 'Center',
                            'right' => 'Right',
                        ])
                        ->default('center'),

                    TextInput::make('content.text_color')
                        ->label('Text Color')
                        ->default('#ffffff')
                        ->helperText('Hex color, e.g., #ffffff'),

                    TextInput::make('content.background_color')
                        ->label('Background Color')
                        ->default('#0f172a')
                        ->helperText('Hex color for banner background when no image'),
                ])->columns(2),
        ];
    }

    /**
     * Text Block: Rich text content
     */
    private static function getTextSchema(): array
    {
        return [
            Section::make('Text Content')
                ->schema([
                    TextInput::make('content.title')
                        ->label('Heading')
                        ->placeholder('Section heading (optional)'),

                    RichEditor::make('content.text')
                        ->label('Rich Text')
                        ->required()
                        ->placeholder('Enter your text content...')
                        ->toolbarButtons([
                            'attachFiles',
                            'blockquote',
                            'bold',
                            'bulletList',
                            'codeBlock',
                            'heading',
                            'italic',
                            'link',
                            'orderedList',
                            'redo',
                            'strike',
                            'underline',
                            'undo',
                        ]),
                ])->columns(1),
        ];
    }

    /**
     * Gallery Block: Image gallery/carousel
     */
    private static function getGallerySchema(): array
    {
        return [
            Section::make('Gallery Content')
                ->schema([
                    TextInput::make('content.title')
                        ->label('Gallery Title')
                        ->placeholder('Optional gallery title'),

                    Select::make('content.layout')
                        ->label('Layout')
                        ->options([
                            'grid' => 'Grid',
                            'carousel' => 'Carousel',
                            'masonry' => 'Masonry',
                        ])
                        ->default('grid'),

                    Select::make('content.columns')
                        ->label('Grid Columns')
                        ->options([
                            '2' => '2 Columns',
                            '3' => '3 Columns',
                            '4' => '4 Columns',
                        ])
                        ->default('3')
                        ->visible(fn ($get) => $get('content.layout') === 'grid'),

                    Repeater::make('content.images')
                        ->label('Images')
                        ->schema([
                            FileUpload::make('url')
                                ->label('Image')
                                ->image()
                                ->maxSize(5120)
                                ->disk('public')
                                ->directory('blocks/galleries')
                                ->required(),

                            TextInput::make('alt')
                                ->label('Alt Text')
                                ->placeholder('Description for accessibility')
                                ->required(),

                            TextInput::make('caption')
                                ->label('Caption')
                                ->placeholder('Optional image caption'),
                        ])
                        ->addActionLabel('Add Image')
                        ->minItems(1),
                ])->columns(2),
        ];
    }

    /**
     * Products Block: Product showcase
     */
    private static function getProductsSchema(): array
    {
        return [
            Section::make('Products Content')
                ->schema([
                    TextInput::make('content.title')
                        ->label('Section Title')
                        ->placeholder('Featured Products'),

                    Textarea::make('content.description')
                        ->label('Description')
                        ->rows(3),

                    Select::make('content.layout')
                        ->label('Layout')
                        ->options([
                            'grid' => 'Grid',
                            'carousel' => 'Carousel',
                            'list' => 'List',
                        ])
                        ->default('grid'),

                    Repeater::make('content.products')
                        ->label('Products')
                        ->schema([
                            TextInput::make('name')
                                ->label('Product Name')
                                ->required(),

                            Textarea::make('description')
                                ->label('Description')
                                ->rows(2),

                            TextInput::make('price')
                                ->label('Price')
                                ->numeric()
                                ->step(0.01)
                                ->prefixIcon('heroicon-m-banknotes'),

                            FileUpload::make('image')
                                ->label('Product Image')
                                ->image()
                                ->disk('public')
                                ->directory('blocks/products'),

                            TextInput::make('button_text')
                                ->label('Button Text')
                                ->default('View Product'),

                            TextInput::make('button_url')
                                ->label('Button URL')
                                ->url(),
                        ])
                        ->addActionLabel('Add Product')
                        ->minItems(1),
                ])->columns(2),
        ];
    }

    /**
     * Reviews Block: Customer reviews/testimonials
     */
    private static function getReviewsSchema(): array
    {
        return [
            Section::make('Reviews Content')
                ->schema([
                    TextInput::make('content.title')
                        ->label('Section Title')
                        ->placeholder('Customer Reviews'),

                    Repeater::make('content.reviews')
                        ->label('Reviews')
                        ->schema([
                            TextInput::make('rating')
                                ->label('Rating')
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(5)
                                ->required(),

                            Textarea::make('text')
                                ->label('Review Text')
                                ->rows(3)
                                ->required(),

                            TextInput::make('author')
                                ->label('Author Name')
                                ->required(),

                            TextInput::make('title')
                                ->label('Review Title'),

                            FileUpload::make('image')
                                ->label('Author Image')
                                ->image()
                                ->disk('public')
                                ->directory('blocks/reviews'),
                        ])
                        ->addActionLabel('Add Review')
                        ->minItems(1),
                ])->columns(1),
        ];
    }

    /**
     * Testimonial Block: Single/featured testimonial
     */
    private static function getTestimonialSchema(): array
    {
        return [
            Section::make('Testimonial Content')
                ->schema([
                    Textarea::make('content.quote')
                        ->label('Quote')
                        ->required()
                        ->rows(4),

                    TextInput::make('content.author')
                        ->label('Author Name')
                        ->required(),

                    TextInput::make('content.author_title')
                        ->label('Author Title')
                        ->placeholder('e.g., CEO at Company'),

                    FileUpload::make('content.author_image')
                        ->label('Author Image')
                        ->image()
                        ->disk('public')
                        ->directory('blocks/testimonials'),

                    TextInput::make('content.company')
                        ->label('Company Name'),
                ])->columns(2),
        ];
    }

    /**
     * CTA Block: Call-to-action section
     */
    private static function getCtaSchema(): array
    {
        return [
            Section::make('CTA Content')
                ->schema([
                    TextInput::make('content.title')
                        ->label('Heading')
                        ->required()
                        ->placeholder('Ready to get started?'),

                    Textarea::make('content.description')
                        ->label('Description')
                        ->rows(3),

                    TextInput::make('content.button_text')
                        ->label('Button Text')
                        ->required()
                        ->placeholder('Click Here'),

                    TextInput::make('content.button_url')
                        ->label('Button URL')
                        ->url()
                        ->required(),

                    Select::make('content.button_style')
                        ->label('Button Style')
                        ->options([
                            'primary' => 'Primary',
                            'secondary' => 'Secondary',
                            'success' => 'Success',
                        ])
                        ->default('primary'),

                    Select::make('content.background_style')
                        ->label('Background Style')
                        ->options([
                            'solid' => 'Solid Color',
                            'gradient' => 'Gradient',
                            'image' => 'Image',
                        ])
                        ->default('solid'),

                    TextInput::make('content.background_color')
                        ->label('Background Color')
                        ->default('#000000'),
                ])->columns(2),
        ];
    }

    /**
     * FAQ Block: Frequently asked questions
     */
    private static function getFaqSchema(): array
    {
        return [
            Section::make('FAQ Content')
                ->schema([
                    TextInput::make('content.title')
                        ->label('Section Title')
                        ->placeholder('Frequently Asked Questions'),

                    Repeater::make('content.items')
                        ->label('Questions')
                        ->schema([
                            TextInput::make('question')
                                ->label('Question')
                                ->required(),

                            RichEditor::make('answer')
                                ->label('Answer')
                                ->required()
                                ->toolbarButtons([
                                    'bold',
                                    'italic',
                                    'link',
                                    'orderedList',
                                    'bulletList',
                                ]),
                        ])
                        ->addActionLabel('Add Question')
                        ->minItems(1),
                ])->columns(1),
        ];
    }

    /**
     * Stats Block: Statistics/numbers display
     */
    private static function getStatsSchema(): array
    {
        return [
            Section::make('Stats Content')
                ->schema([
                    TextInput::make('content.title')
                        ->label('Section Title')
                        ->placeholder('Our Stats'),

                    Repeater::make('content.stats')
                        ->label('Statistics')
                        ->schema([
                            TextInput::make('number')
                                ->label('Number/Value')
                                ->required()
                                ->placeholder('e.g., 1000'),

                            TextInput::make('label')
                                ->label('Label')
                                ->required()
                                ->placeholder('e.g., Happy Customers'),

                            TextInput::make('suffix')
                                ->label('Suffix')
                                ->placeholder('e.g., %'),

                            TextInput::make('color')
                                ->label('Icon Color')
                                ->placeholder('e.g., blue, green, red'),
                        ])
                        ->addActionLabel('Add Statistic')
                        ->minItems(1),
                ])->columns(2),
        ];
    }

    /**
     * Newsletter Block: Email signup form
     */
    private static function getNewsletterSchema(): array
    {
        return [
            Section::make('Newsletter Content')
                ->schema([
                    TextInput::make('content.title')
                        ->label('Heading')
                        ->required()
                        ->placeholder('Subscribe to Our Newsletter'),

                    Textarea::make('content.description')
                        ->label('Description')
                        ->rows(2),

                    TextInput::make('content.button_text')
                        ->label('Button Text')
                        ->default('Subscribe'),

                    TextInput::make('content.placeholder')
                        ->label('Email Placeholder')
                        ->default('Enter your email'),

                    TextInput::make('content.success_message')
                        ->label('Success Message')
                        ->default('Thank you for subscribing!'),

                    Toggle::make('content.show_privacy')
                        ->label('Show Privacy Notice')
                        ->default(true),
                ])->columns(2),
        ];
    }

    /**
     * Contact Form Block: Contact form
     */
    private static function getContactFormSchema(): array
    {
        return [
            Section::make('Contact Form Content')
                ->schema([
                    TextInput::make('content.title')
                        ->label('Form Title')
                        ->required()
                        ->placeholder('Get in Touch'),

                    Textarea::make('content.description')
                        ->label('Description')
                        ->rows(2),

                    TextInput::make('content.button_text')
                        ->label('Submit Button Text')
                        ->default('Send Message'),

                    TextInput::make('content.success_message')
                        ->label('Success Message')
                        ->default('Thank you for your message!'),

                    Repeater::make('content.fields')
                        ->label('Form Fields')
                        ->schema([
                            TextInput::make('label')
                                ->label('Field Label')
                                ->required(),

                            Select::make('type')
                                ->label('Field Type')
                                ->options([
                                    'text' => 'Text',
                                    'email' => 'Email',
                                    'phone' => 'Phone',
                                    'textarea' => 'Textarea',
                                    'select' => 'Select',
                                ])
                                ->required(),

                            Toggle::make('required')
                                ->label('Required')
                                ->default(true),

                            TextInput::make('placeholder')
                                ->label('Placeholder'),
                        ])
                        ->addActionLabel('Add Field')
                        ->defaultItems(4)
                        ->minItems(1),
                ])->columns(2),
        ];
    }

    /**
     * Default schema for unknown block types
     */
    private static function getDefaultSchema(): array
    {
        return [
            Section::make('Block Content')
                ->schema([
                    TextInput::make('content.title')
                        ->label('Title'),

                    Textarea::make('content.description')
                        ->label('Description')
                        ->rows(4),

                    TextInput::make('content.image_url')
                        ->label('Image URL')
                        ->url(),

                    TextInput::make('content.button_text')
                        ->label('Button Text'),

                    TextInput::make('content.button_url')
                        ->label('Button URL')
                        ->url(),
                ])->columns(2),
        ];
    }
}
