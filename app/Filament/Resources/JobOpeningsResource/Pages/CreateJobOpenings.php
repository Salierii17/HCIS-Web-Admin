<?php

namespace App\Filament\Resources\JobOpeningsResource\Pages;

use App\Filament\Resources\JobOpeningsResource;
use Filament\Resources\Pages\CreateRecord;
use Carbon\Carbon;

class CreateJobOpenings extends CreateRecord
{
    protected static string $resource = JobOpeningsResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['Status'] = 'New';
        
        // Ensure dates are properly formatted (though DateTimePicker should handle this)
        $data['TargetDate'] = Carbon::parse($data['TargetDate'])->format('Y-m-d H:i:s');
        $data['DateOpened'] = Carbon::parse($data['DateOpened'])->format('Y-m-d H:i:s');
        
        // Process rich text fields
        $richTextFields = ['JobDescription', 'JobRequirement', 'JobBenefits', 'AdditionalNotes'];
        foreach ($richTextFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = $this->cleanRichText($data[$field]);
            }
        }

        return $data;
    }

    protected function cleanRichText(string $content): string
    {
        $content = preg_replace('/<p[^>]*>\s*<\/p>/', '', $content);
        $content = preg_replace('/<p[^>]*>/', '', $content);
        $content = str_replace('</p>', '<br><br>', $content);
        $content = preg_replace('/<[^\/>]*>([\s]?)*<\/[^>]*>/', '', $content);
        return trim($content);
    }
}