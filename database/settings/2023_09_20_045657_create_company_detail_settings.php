<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('company.site_name', 'BNI Sekuritas - Careers');
        $this->migrator->add('company.company_name', 'BNI Sekuritas');
        $this->migrator->add('company.company_website', 'www.bnisekuritas.co.id');
        $this->migrator->add('company.company_primary_contact_email', 'customercare@bnisekuritas.co.id');
        $this->migrator->add('company.company_employee_count', 255);
        $this->migrator->add('company.company_country', 'Indonesia');
        $this->migrator->add('company.company_state', 'DKI Jakarta');
        $this->migrator->add('company.company_city', 'South Jakarta');

    }
};
