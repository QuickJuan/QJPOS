<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.enable_feedback_qr_code', false);
    }

    public function down(): void
    {
        $this->migrator->delete('general.enable_feedback_qr_code');
    }
};
