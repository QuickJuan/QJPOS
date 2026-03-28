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
use Filament\Forms\Components\ColorPicker;

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
            'features' => self::getFeaturesSchema(),
            'newsletter' => self::getNewsletterSchema(),
            'contact-form' => self::getContactFormSchema(),
            'product-list' => self::getProductListSchema(),
            'careers' => self::getCareersSchema(),
            'articles' => self::getArticlesSchema(),
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

                    ColorPicker::make('content.text_color')
                        ->label('Text Color (fallback for heading & subtitle)')
                        ->default('#ffffff')
                        ->helperText('Used when heading/subtitle colors are not set.'),

                    ColorPicker::make('content.heading_color')
                        ->label('Heading Color')
                        ->helperText('Specific color for the main headline only. Defaults to Text Color.'),

                    ColorPicker::make('content.subtitle_color')
                        ->label('Subtitle Color')
                        ->helperText('Specific color for the subtitle only. Defaults to Text Color.'),

                    ColorPicker::make('content.background_color')
                        ->label('Background Color')
                        ->default('#0f172a')
                        ->helperText('Used when no background image is set.'),

                    ColorPicker::make('content.overlay_color')
                        ->label('Overlay Color')
                        ->default('#000000')
                        ->helperText('Color tinted over the image, combined with Overlay Opacity.'),
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
                            'grid'     => 'Grid',
                            'carousel' => 'Carousel',
                            'list'     => 'List',
                        ])
                        ->default('grid'),

                    TextInput::make('settings.max_products')
                        ->label('Max Products to Show')
                        ->numeric()
                        ->minValue(1)
                        ->placeholder('Leave empty to show all'),
                ])->columns(2),

            Section::make('Appearance')
                ->schema([
                    ColorPicker::make('settings.bg_color')
                        ->label('Section Background Color')
                        ->default('#020617')
                        ->helperText('Applied to the whole section background.'),

                    ColorPicker::make('settings.text_color')
                        ->label('Heading & Description Color')
                        ->default('#ffffff')
                        ->helperText('Color for the section title and description text.'),
                ])->columns(2),

            Section::make('Product Filters')
                ->description('Select which categories and/or groups to pull products from. Leave both empty to show all active products.')
                ->schema([
                    Select::make('settings.category_ids')
                        ->label('Filter by Categories')
                        ->multiple()
                        ->options(fn () => \App\Models\Category::orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->placeholder('All categories'),

                    Select::make('settings.group_ids')
                        ->label('Filter by Groups')
                        ->multiple()
                        ->options(fn () => \App\Models\Group::orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->placeholder('All groups'),

                    Select::make('settings.product_ids')
                        ->label('Specific Products')
                        ->multiple()
                        ->options(fn () => \App\Models\Product::where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->placeholder('Leave empty to use category/group filters')
                        ->helperText('Selecting specific products overrides category and group filters.'),
                ]),
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
     * Features Block: Feature grid with icons, title, and description
     */
    private static function getFeaturesSchema(): array
    {
        return [
            Section::make('Features Content')
                ->schema([
                    TextInput::make('content.title')
                        ->label('Section Heading')
                        ->placeholder('Why choose us?'),

                    Textarea::make('content.subtitle')
                        ->label('Subheading')
                        ->rows(2)
                        ->placeholder('A short description below the heading'),

                    Select::make('settings.columns')
                        ->label('Columns per row')
                        ->options(['2' => '2 Columns', '3' => '3 Columns', '4' => '4 Columns'])
                        ->default('3'),

                    Repeater::make('content.features')
                        ->label('Features')
                        ->schema([
                            FileUpload::make('image')
                                ->label('Feature Image')
                                ->image()
                                ->disk('public')
                                ->directory('blocks/features')
                                ->imageResizeMode('cover')
                                ->imageCropAspectRatio('1:1')
                                ->columnSpanFull(),

                            TextInput::make('title')
                                ->label('Title')
                                ->required()
                                ->placeholder('e.g. Fast Delivery'),

                            Textarea::make('description')
                                ->label('Description')
                                ->rows(2)
                                ->placeholder('Short description of this feature'),
                        ])
                        ->addActionLabel('Add Feature')
                        ->minItems(1)
                        ->maxItems(12)
                        ->columnSpanFull(),
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
     * Product List Block: Full ecommerce-style listing with client-side search/filter
     */
    private static function getProductListSchema(): array
    {
        return [
            Section::make('Section Header')
                ->schema([
                    TextInput::make('content.title')
                        ->label('Section Title')
                        ->placeholder('Our Menu')
                        ->columnSpan(2),

                    TextInput::make('content.subtitle')
                        ->label('Tag Line (small text above title)')
                        ->placeholder('Explore our selection')
                        ->columnSpan(2),

                    Textarea::make('content.description')
                        ->label('Description (below title)')
                        ->rows(2)
                        ->columnSpan(2),
                ])->columns(2),

            Section::make('Filter Layout')
                ->schema([
                    Select::make('settings.filter_layout')
                        ->label('Filter Display Style')
                        ->options([
                            'tags'    => 'Tags — clickable pill tags above the grid',
                            'sidebar' => 'Sidebar — categories listed on the left column',
                        ])
                        ->default('tags')
                        ->helperText('Controls how category and group filters are presented to customers.'),
                ]),

            Section::make('Appearance')
                ->schema([
                    ColorPicker::make('settings.bg_color')
                        ->label('Section Background Color')
                        ->default('#020617')
                        ->helperText('Applied to the whole section background.'),

                    ColorPicker::make('settings.text_color')
                        ->label('Heading & Description Color')
                        ->default('#ffffff')
                        ->helperText('Color for the section title, subtitle, and description.'),
                ])->columns(2),

            Section::make('Product Card Colors')
                ->schema([
                    ColorPicker::make('settings.card_bg_color')
                        ->label('Card Background')
                        ->default('#0f172a')
                        ->helperText('Background color of each product card.'),

                    ColorPicker::make('settings.card_title_color')
                        ->label('Card Title Color')
                        ->default('#ffffff')
                        ->helperText('Product name text color.'),

                    ColorPicker::make('settings.card_desc_color')
                        ->label('Card Description Color')
                        ->default('#94a3b8')
                        ->helperText('Product description text color.'),

                    ColorPicker::make('settings.card_price_color')
                        ->label('Price Color')
                        ->default('#fb923c')
                        ->helperText('Price text color on each card.'),
                ])->columns(2),

            Section::make('Filter Tag Colors')
                ->schema([
                    ColorPicker::make('settings.tag_color')
                        ->label('Tag Text Color (inactive)')
                        ->default('#94a3b8')
                        ->helperText('Text color of unselected filter tags/pills.'),

                    ColorPicker::make('settings.tag_active_bg')
                        ->label('Active Tag Background')
                        ->default('#f97316')
                        ->helperText('Background color of the selected/active filter tag.'),

                    ColorPicker::make('settings.tag_active_text')
                        ->label('Active Tag Text Color')
                        ->default('#ffffff')
                        ->helperText('Text color of the selected/active filter tag.'),
                ])->columns(2),

            Section::make('Display Settings')
                ->schema([
                    TextInput::make('settings.page_size')
                        ->label('Products per page (load-more)')
                        ->numeric()
                        ->minValue(4)
                        ->default(12)
                        ->helperText('How many products to show before the "Load more" button.'),

                    Toggle::make('settings.show_add_to_cart')
                        ->label('Show "Add to Cart" button')
                        ->default(true)
                        ->helperText('Toggle to hide the cart button (e.g. for browse-only menus).'),
                ])->columns(2),
        ];
    }

    /**
     * Blog Block: auto-loaded blog posts
     */
    private static function getArticlesSchema(): array
    {
        return [
            Section::make('Blog Block')
                ->schema([
                    TextInput::make('content.title')
                        ->label('Section Heading')
                        ->placeholder('Latest Blog Posts'),

                    Textarea::make('content.subtitle')
                        ->label('Subheading')
                        ->rows(2)
                        ->placeholder('Stay up to date with our latest posts'),

                    TextInput::make('settings.limit')
                        ->label('Max Posts to Show')
                        ->numeric()
                        ->minValue(1)
                        ->placeholder('Leave empty to show all'),
                ])->columns(1)
                ->description('Automatically displays published Blog Post pages, newest first.'),
        ];
    }

    /**
     * Default schema for unknown block types
     */
    private static function getCareersSchema(): array
    {
        return [
            Section::make('Careers Block')
                ->schema([
                    TextInput::make('content.title')
                        ->label('Section Heading')
                        ->placeholder('We are Hiring!'),

                    Textarea::make('content.subtitle')
                        ->label('Subheading')
                        ->rows(2)
                        ->placeholder('Join our growing team'),
                ])->columns(1)
                ->description('The careers block automatically displays all job openings with status "Available". No manual configuration needed.'),
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
