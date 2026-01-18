<?php

namespace App\Enums;

enum PageType: string
{
    case PAGE = 'page';
    case PRODUCT = 'product';
    case BLOG = 'blog';
    case FAQ = 'faq';
    case LANDING_PAGE = 'landing_page';

    public function label(): string
    {
        return match ($this) {
            self::PAGE => 'Page',
            self::PRODUCT => 'Product',
            self::BLOG => 'Blog Post',
            self::FAQ => 'FAQ',
            self::LANDING_PAGE => 'Landing Page',
        };
    }

    public static function options(): array
    {
        return [
            self::PAGE->value => self::PAGE->label(),
            self::PRODUCT->value => self::PRODUCT->label(),
            self::BLOG->value => self::BLOG->label(),
            self::FAQ->value => self::FAQ->label(),
            self::LANDING_PAGE->value => self::LANDING_PAGE->label(),
        ];
    }

    public function urlPrefix(): ?string
    {
        return match ($this) {
            self::PRODUCT => 'products',
            self::BLOG => 'blogs',
            self::FAQ => 'faq',
            self::PAGE => null,
            self::LANDING_PAGE => null,
        };
    }
}
