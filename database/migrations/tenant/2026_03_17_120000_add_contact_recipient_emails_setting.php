<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.contact_recipient_emails', '');
    }

    public function down(): void
    {
        $this->migrator->delete('general.contact_recipient_emails');
    }
};
