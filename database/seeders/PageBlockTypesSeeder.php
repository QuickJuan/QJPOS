<?php

namespace Database\Seeders;

use App\Models\PageBlockType;
use Illuminate\Database\Seeder;

class PageBlockTypesSeeder extends Seeder
{
    /**
     * Seed predefined page block types used by the page builder.
     */
    public function run(): void
    {
        $blockTypes = [
            [
                'name' => 'Banner Hero',
                'slug' => 'banner',
                'icon' => 'photo',
                'description' => 'Full-width hero banner with headline, subtitle, background image, and call-to-action.',
                'category' => 'hero',
                'component_name' => 'BannerBlock',
                'schema_template' => [
                    'content' => [
                        'title' => 'Your next favorite place',
                        'subtitle' => 'Describe the key value or promise.',
                        'image' => null,
                        'overlay_opacity' => 0.25,
                        'alignment' => 'center',
                        'text_color' => '#ffffff',
                        'background_color' => '#0f172a',
                        'button_text' => 'Get started',
                        'button_url' => '#cta',
                        'button_style' => 'primary',
                    ],
                ],
                'settings_schema' => [
                    'layout' => 'center',
                    'padding' => 'xl',
                    'background' => 'light',
                ],
            ],
            [
                'name' => 'Text',
                'slug' => 'text',
                'icon' => 'document-text',
                'description' => 'Rich text/content section with optional heading.',
                'category' => 'content',
                'component_name' => 'TextBlock',
                'schema_template' => [
                    'content' => [
                        'title' => 'Section heading',
                        'text' => '<p>Add your story here.</p>',
                    ],
                ],
                'settings_schema' => [
                    'padding' => 'md',
                    'background' => 'white',
                ],
            ],
            [
                'name' => 'Gallery / Carousel',
                'slug' => 'gallery',
                'icon' => 'rectangle-stack',
                'description' => 'Image gallery that can render as grid, carousel, or masonry.',
                'category' => 'media',
                'component_name' => 'GalleryBlock',
                'schema_template' => [
                    'content' => [
                        'title' => 'Gallery',
                        'layout' => 'carousel',
                        'columns' => '3',
                        'images' => [
                            ['url' => null, 'alt' => 'Image one', 'caption' => null],
                            ['url' => null, 'alt' => 'Image two', 'caption' => null],
                        ],
                    ],
                ],
                'settings_schema' => [
                    'padding' => 'lg',
                    'background' => 'white',
                ],
            ],
            [
                'name' => 'Products',
                'slug' => 'products',
                'icon' => 'shopping-bag',
                'description' => 'Showcase featured products in grid, carousel, or list.',
                'category' => 'commerce',
                'component_name' => 'ProductsBlock',
                'schema_template' => [
                    'content' => [
                        'title'       => 'Featured Products',
                        'description' => 'Highlight best sellers or promos.',
                        'layout'      => 'grid',
                    ],
                    'settings' => [
                        'category_ids' => [],
                        'group_ids'    => [],
                        'max_products' => null,
                        'padding'      => 'lg',
                        'background'   => 'dark',
                    ],
                ],
                'settings_schema' => [
                    'category_ids' => [],
                    'group_ids'    => [],
                    'max_products' => null,
                    'padding'      => 'lg',
                    'background'   => 'dark',
                ],
            ],
            [
                'name' => 'Reviews',
                'slug' => 'reviews',
                'icon' => 'chat-bubble-left-right',
                'description' => 'Carousel or list of customer reviews.',
                'category' => 'social-proof',
                'component_name' => 'ReviewsBlock',
                'schema_template' => [
                    'content' => [
                        'title' => 'What customers say',
                        'reviews' => [
                            [
                                'rating' => 5,
                                'text' => 'This is amazing.',
                                'author' => 'Jane Doe',
                                'title' => 'Regular customer',
                                'image' => null,
                            ],
                        ],
                    ],
                ],
                'settings_schema' => [
                    'layout' => 'carousel',
                    'padding' => 'lg',
                ],
            ],
            [
                'name' => 'Testimonial',
                'slug' => 'testimonial',
                'icon' => 'sparkles',
                'description' => 'Single highlighted testimonial with author details.',
                'category' => 'social-proof',
                'component_name' => 'TestimonialBlock',
                'schema_template' => [
                    'content' => [
                        'quote' => 'Add a standout testimonial here.',
                        'author' => 'Happy Customer',
                        'author_title' => 'Role / Company',
                        'author_image' => null,
                        'company' => 'Company name',
                    ],
                ],
                'settings_schema' => [
                    'padding' => 'lg',
                    'background' => 'light',
                ],
            ],
            [
                'name' => 'Call to Action',
                'slug' => 'cta',
                'icon' => 'megaphone',
                'description' => 'Call-to-action strip with button and optional background styling.',
                'category' => 'conversion',
                'component_name' => 'CtaBlock',
                'schema_template' => [
                    'content' => [
                        'title' => 'Ready to order?',
                        'description' => 'Nudge visitors to take the next step.',
                        'button_text' => 'Order now',
                        'button_url' => '#order',
                        'button_style' => 'primary',
                        'background_style' => 'solid',
                        'background_color' => '#f97316',
                    ],
                ],
                'settings_schema' => [
                    'padding' => 'md',
                    'background' => 'brand',
                ],
            ],
            [
                'name' => 'FAQ',
                'slug' => 'faq',
                'icon' => 'question-mark-circle',
                'description' => 'Accordion of frequently asked questions.',
                'category' => 'content',
                'component_name' => 'FaqBlock',
                'schema_template' => [
                    'content' => [
                        'title' => 'Frequently asked questions',
                        'items' => [
                            [
                                'question' => 'How does this work?',
                                'answer' => '<p>Answer goes here.</p>',
                            ],
                        ],
                    ],
                ],
                'settings_schema' => [
                    'padding' => 'md',
                ],
            ],
            [
                'name' => 'Stats',
                'slug' => 'stats',
                'icon' => 'presentation-chart-bar',
                'description' => 'Highlights key metrics or KPIs.',
                'category' => 'content',
                'component_name' => 'StatsBlock',
                'schema_template' => [
                    'content' => [
                        'title' => 'By the numbers',
                        'stats' => [
                            ['number' => '120', 'label' => 'Branches', 'suffix' => null, 'color' => 'amber'],
                            ['number' => '4.9', 'label' => 'Rating', 'suffix' => '/5', 'color' => 'emerald'],
                        ],
                    ],
                ],
                'settings_schema' => [
                    'padding' => 'md',
                ],
            ],
            [
                'name' => 'Newsletter',
                'slug' => 'newsletter',
                'icon' => 'envelope',
                'description' => 'Email signup form with success/consent copy.',
                'category' => 'conversion',
                'component_name' => 'NewsletterBlock',
                'schema_template' => [
                    'content' => [
                        'title' => 'Stay in the loop',
                        'description' => 'Get the latest promos and menu drops.',
                        'button_text' => 'Subscribe',
                        'placeholder' => 'you@example.com',
                        'success_message' => 'Thanks for subscribing!',
                        'show_privacy' => true,
                    ],
                ],
                'settings_schema' => [
                    'padding' => 'md',
                    'background' => 'light',
                ],
            ],
            [
                'name' => 'Contact Form',
                'slug' => 'contact-form',
                'icon' => 'inbox',
                'description' => 'Flexible contact form with configurable fields.',
                'category' => 'conversion',
                'component_name' => 'ContactFormBlock',
                'schema_template' => [
                    'content' => [
                        'title' => 'Get in touch',
                        'description' => 'We usually reply within one business day.',
                        'button_text' => 'Send message',
                        'success_message' => 'Thanks for reaching out!',
                        'fields' => [
                            ['label' => 'Name', 'type' => 'text', 'required' => true, 'placeholder' => 'Your name'],
                            ['label' => 'Email', 'type' => 'email', 'required' => true, 'placeholder' => 'you@example.com'],
                            ['label' => 'Phone', 'type' => 'phone', 'required' => false, 'placeholder' => '+1 555 123 4567'],
                            ['label' => 'Message', 'type' => 'textarea', 'required' => true, 'placeholder' => 'How can we help?'],
                        ],
                    ],
                ],
                'settings_schema' => [
                    'padding' => 'md',
                    'background' => 'white',
                ],
            ],
            [
                'name' => 'Product List',
                'slug' => 'product-list',
                'icon' => 'shopping-bag',
                'description' => 'Ecommerce-style product listing with client-side search, category & group filters, and optional Add-to-Cart button.',
                'category' => 'commerce',
                'component_name' => 'ProductListBlock',
                'schema_template' => [
                    'content' => [
                        'title' => 'Our Products',
                        'description' => 'Browse our full selection',
                    ],
                    'settings' => [
                        'show_add_to_cart' => true,
                        'page_size' => 12,
                    ],
                ],
                'settings_schema' => [
                    'show_add_to_cart' => true,
                    'page_size' => 12,
                ],
            ],
            [
                'name' => 'Features',
                'slug' => 'features',
                'icon' => 'sparkles',
                'description' => 'Feature grid with icon circles, title, and description for each item.',
                'category' => 'content',
                'component_name' => 'FeaturesBlock',
                'schema_template' => [
                    'content' => [
                        'title' => 'Why choose us?',
                        'subtitle' => '',
                        'features' => [
                            ['icon' => '🍕', 'title' => 'Fresh Ingredients', 'description' => 'We use only the freshest ingredients sourced daily.', 'icon_color' => 'orange'],
                            ['icon' => '⚡', 'title' => 'Fast Delivery', 'description' => 'Hot food at your door in 30 minutes or less.', 'icon_color' => 'yellow'],
                            ['icon' => '💰', 'title' => 'Great Value', 'description' => 'Enjoy restaurant-quality meals at affordable prices.', 'icon_color' => 'green'],
                        ],
                    ],
                ],
                'settings_schema' => [
                    'columns' => '3',
                ],
            ],
            [
                'name' => 'Careers',
                'slug' => 'careers',
                'icon' => 'briefcase',
                'description' => 'Job opening cards auto-loaded from available careers.',
                'category' => 'content',
                'component_name' => 'CareersBlock',
                'schema_template' => [
                    'content' => [
                        'title' => 'Join Our Team',
                        'subtitle' => 'Explore open positions and grow with us.',
                    ],
                ],
                'settings_schema' => [],
            ],
        ];

        foreach ($blockTypes as $blockType) {
            $slug = $blockType['slug'];
            PageBlockType::updateOrCreate(
                ['slug' => $slug],
                collect($blockType)->except(['slug'])->toArray()
            );
        }
    }
}
