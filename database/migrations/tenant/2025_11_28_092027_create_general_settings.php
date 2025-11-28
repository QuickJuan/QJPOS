<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.company_name');
        $this->migrator->add('general.company_address');
        $this->migrator->add('general.company_contact');
        $this->migrator->add('general.company_logo');
    }

    public function down()
    {
        $this->migrator->delete('general.company_name');
        $this->migrator->delete('general.company_address');
        $this->migrator->delete('general.company_contact');
        $this->migrator->delete('general.company_logo');
    }
};
