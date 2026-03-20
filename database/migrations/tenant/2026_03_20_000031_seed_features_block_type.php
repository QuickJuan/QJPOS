<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\PageBlockType;

return new class extends Migration
{
    public function up(): void
    {
        PageBlockType::updateOrCreate(
            ['slug' => 'features'],
            [
                'name'            => 'Features',
                'icon'            => 'sparkles',
                'description'     => 'Feature grid with icon circles, title, and description for each item.',
                'category'        => 'content',
                'component_name'  => 'FeaturesBlock',
                'schema_template' => [
                    'content' => [
                        'title'    => 'Why choose us?',
                        'subtitle' => '',
                        'features' => [
                            ['icon' => '🍕', 'title' => 'Fresh Ingredients', 'description' => 'We use only the freshest ingredients sourced daily.',    'icon_color' => 'orange'],
                            ['icon' => '⚡', 'title' => 'Fast Delivery',     'description' => 'Hot food at your door in 30 minutes or less.',           'icon_color' => 'yellow'],
                            ['icon' => '💰', 'title' => 'Great Value',       'description' => 'Enjoy restaurant-quality meals at affordable prices.',   'icon_color' => 'green'],
                        ],
                    ],
                ],
                'settings_schema' => [
                    'columns' => '3',
                ],
            ]
        );
    }

    public function down(): void
    {
        PageBlockType::where('slug', 'features')->delete();
    }
};
