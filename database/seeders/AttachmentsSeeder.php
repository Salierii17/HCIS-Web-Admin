<?php

namespace Database\Seeders;

use App\Filament\Enums\AttachmentCategory;
use App\Models\Attachments;
use App\Models\Candidates;
use App\Models\JobCandidates;
use App\Models\JobOpenings;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class AttachmentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding Attachments...');

        // Create storage directories if they don't exist
        Storage::disk('public')->makeDirectory('JobOpening-attachments');
        Storage::disk('public')->makeDirectory('candidate-resumes');

        // Create sample PDF files
        $this->createSampleFiles();

        // Seed JobOpening attachments
        $this->seedJobOpeningAttachments();

        // Seed JobCandidate attachments (resumes)
        $this->seedJobCandidateAttachments();

        // Seed Candidate profile attachments
        $this->seedCandidateAttachments();

        $this->command->info('Attachments seeded successfully!');
    }

    private function createSampleFiles(): void
    {
        $sampleContent = "%PDF-1.4\n1 0 obj\n<<\n/Type /Catalog\n/Pages 2 0 R\n>>\nendobj\n2 0 obj\n<<\n/Type /Pages\n/Kids [3 0 R]\n/Count 1\n>>\nendobj\n3 0 obj\n<<\n/Type /Page\n/Parent 2 0 R\n/MediaBox [0 0 612 792]\n/Contents 4 0 R\n>>\nendobj\n4 0 obj\n<<\n/Length 44\n>>\nstream\nBT\n/F1 12 Tf\n100 700 Td\n(Sample Document) Tj\nET\nendstream\nendobj\nxref\n0 5\n0000000000 65535 f \n0000000009 00000 n \n0000000058 00000 n \n0000000115 00000 n \n0000000206 00000 n \ntrailer\n<<\n/Size 5\n/Root 1 0 R\n>>\nstartxref\n299\n%%EOF";
        
        // Create sample files
        $files = [
            'job-description-1.pdf',
            'job-requirements-2.pdf', 
            'company-policy.pdf',
            'resume-john-doe.pdf',
            'resume-jane-smith.pdf',
            'resume-mike-johnson.pdf',
            'cover-letter-sample.pdf'
        ];

        foreach ($files as $filename) {
            Storage::disk('public')->put('JobOpening-attachments/' . $filename, $sampleContent);
        }
    }

    private function seedJobOpeningAttachments(): void
    {
        $jobOpenings = JobOpenings::limit(3)->get();

        foreach ($jobOpenings as $index => $jobOpening) {
            // Create 1-2 attachments per job opening
            $attachmentCount = rand(1, 2);
            
            for ($i = 0; $i < $attachmentCount; $i++) {
                $filename = match($i) {
                    0 => "job-description-{$jobOpening->id}.pdf",
                    1 => "job-requirements-{$jobOpening->id}.pdf",
                    default => "company-policy.pdf"
                };

                Attachments::create([
                    'attachment' => 'JobOpening-attachments/' . $filename,
                    'attachmentName' => $filename,
                    'category' => $i === 0 ? AttachmentCategory::JobSummary : AttachmentCategory::Others,
                    'attachmentOwner' => $jobOpening->id,
                    'moduleName' => 'JobOpening'
                ]);
            }
        }

        $this->command->info('JobOpening attachments created.');
    }

    private function seedJobCandidateAttachments(): void
    {
        $jobCandidates = JobCandidates::limit(10)->get();

        foreach ($jobCandidates as $jobCandidate) {
            // Each job candidate gets a resume attachment
            Attachments::create([
                'attachment' => 'JobOpening-attachments/resume-candidate-' . $jobCandidate->id . '.pdf',
                'attachmentName' => 'resume-candidate-' . $jobCandidate->id . '.pdf',
                'category' => AttachmentCategory::Resume,
                'attachmentOwner' => $jobCandidate->id,
                'moduleName' => 'JobCandidates'
            ]);

            // Some get additional documents
            if (rand(1, 3) === 1) {
                Attachments::create([
                    'attachment' => 'JobOpening-attachments/cover-letter-' . $jobCandidate->id . '.pdf',
                    'attachmentName' => 'cover-letter-' . $jobCandidate->id . '.pdf',
                    'category' => AttachmentCategory::CoverLetter,
                    'attachmentOwner' => $jobCandidate->id,
                    'moduleName' => 'JobCandidates'
                ]);
            }
        }

        $this->command->info('JobCandidate attachments created.');
    }

    private function seedCandidateAttachments(): void
    {
        $candidates = Candidates::limit(10)->get();

        foreach ($candidates as $candidate) {
            // Each candidate profile gets a resume
            Attachments::create([
                'attachment' => 'JobOpening-attachments/profile-resume-' . $candidate->id . '.pdf',
                'attachmentName' => 'profile-resume-' . $candidate->id . '.pdf',
                'category' => AttachmentCategory::Resume,
                'attachmentOwner' => $candidate->id,
                'moduleName' => 'Candidates'
            ]);

            // Some get portfolio or certificates
            if (rand(1, 2) === 1) {
                Attachments::create([
                    'attachment' => 'JobOpening-attachments/certificate-' . $candidate->id . '.pdf',
                    'attachmentName' => 'certificate-' . $candidate->id . '.pdf',
                    'category' => AttachmentCategory::Others,
                    'attachmentOwner' => $candidate->id,
                    'moduleName' => 'Candidates'
                ]);
            }
        }

        $this->command->info('Candidate profile attachments created.');
    }
}
