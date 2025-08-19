<?php

namespace Database\Seeders;

use App\Models\Material;
use Illuminate\Database\Seeder;

class MaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->warn(PHP_EOL.'Creating Materials...');

        $materials = [
            ['nama_material' => 'Investment Banking Pitchbook Template', 'file_path' => 'materials/ib_pitchbook_template.pdf'],
            ['nama_material' => 'Equity Research Valuation Model', 'file_path' => 'materials/er_valuation_model.xlsx'],
            ['nama_material' => 'Sales & Trading Market Commentary Guide', 'file_path' => 'materials/st_market_commentary.pdf'],
            ['nama_material' => 'OJK Compliance Checklist 2024', 'file_path' => 'materials/ojk_compliance_checklist.pdf'],
            ['nama_material' => 'Cybersecurity Policy for Financial Institutions', 'file_path' => 'materials/tech_cybersecurity_policy.pdf'],
            ['nama_material' => 'Wealth Management Client Onboarding Kit', 'file_path' => 'materials/wm_client_onboarding.pdf'],
        ];

        foreach ($materials as $material) {
            Material::firstOrCreate($material);
        }
        $this->command->info('Materials created successfully.');
    }
}
