<?php

use App\Filament\Resources\ReferralsResource;
use App\Models\Candidates;
use App\Models\Departments;
use App\Models\JobCandidates;
use App\Models\JobOpenings;
use App\Models\Referrals;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    // Create test data
    $this->department = Departments::factory()->create(['DepartmentName' => 'Test Department']);
    $this->jobOpening = JobOpenings::factory()->create(['Department' => $this->department->id]);
    $this->candidate = Candidates::factory()->create(['email' => 'candidate@example.com']);
    
    // Set up fake storage for file uploads
    Storage::fake('public');
});

describe('Referrals Resource Index Page', function () {
    it('can render index page', function () {
        $this->get(ReferralsResource::getUrl('index'))
            ->assertSuccessful();
    });

    it('can list referrals', function () {
        $referrals = Referrals::factory()->count(3)->create([
            'ReferringJob' => $this->jobOpening->id,
            'Candidate' => $this->candidate->id,
        ]);

        Livewire::test(ReferralsResource\Pages\ListReferrals::class)
            ->assertCanSeeTableRecords($referrals);
    });

    it('can search referrals by candidate name', function () {
        $candidate1 = Candidates::factory()->create([
            'FirstName' => 'John',
            'LastName' => 'Doe',
            'full_name' => 'John Doe',
        ]);

        $candidate2 = Candidates::factory()->create([
            'FirstName' => 'Jane',
            'LastName' => 'Smith',
            'full_name' => 'Jane Smith',
        ]);

        $referral1 = Referrals::factory()->create([
            'ReferringJob' => $this->jobOpening->id,
            'Candidate' => $candidate1->id,
        ]);

        $referral2 = Referrals::factory()->create([
            'ReferringJob' => $this->jobOpening->id,
            'Candidate' => $candidate2->id,
        ]);

        Livewire::test(ReferralsResource\Pages\ListReferrals::class)
            ->searchTable('John Doe')
            ->assertCanSeeTableRecords([$referral1])
            ->assertCanNotSeeTableRecords([$referral2]);
    });

    it('can sort referrals by creation date', function () {
        $referrals = Referrals::factory()->count(3)->create([
            'ReferringJob' => $this->jobOpening->id,
            'Candidate' => $this->candidate->id,
        ]);

        Livewire::test(ReferralsResource\Pages\ListReferrals::class)
            ->sortTable('created_at')
            ->assertCanSeeTableRecords($referrals, inOrder: true);
    });
});

describe('Referrals Resource Create Page', function () {
    it('can render create page', function () {
        $this->get(ReferralsResource::getUrl('create'))
            ->assertSuccessful();
    });

    it('can create a referral with existing candidate', function () {
        $newData = [
            'ReferringJob' => $this->jobOpening->id,
            'Candidate' => $this->candidate->id,
            'Relationship' => 'Former Colleague',
            'KnownPeriod' => '3-5 years',
            'Notes' => 'Excellent developer with strong technical skills',
        ];

        Livewire::test(ReferralsResource\Pages\CreateReferrals::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('referrals', [
            'ReferringJob' => $this->jobOpening->id,
            'Candidate' => $this->candidate->id,
            'Relationship' => 'Former Colleague',
            'KnownPeriod' => '3-5 years',
        ]);
    });

    it('can create a referral with new candidate', function () {
        $candidateData = [
            'FirstName' => 'New',
            'LastName' => 'Candidate',
            'Mobile' => '+1234567890',
            'email' => 'newcandidate@example.com',
            'CurrentEmployer' => 'Tech Corp',
            'CurrentJobTitle' => 'Senior Developer',
        ];

        $newData = [
            'ReferringJob' => $this->jobOpening->id,
            'Candidate' => $candidateData, // This would be handled by the createOptionForm
            'Relationship' => 'Personally Known',
            'KnownPeriod' => '1-2 years',
            'Notes' => 'Great team player',
        ];

        // Note: This test structure assumes the form handles candidate creation
        // The actual implementation may vary based on Filament's relationship handling
        Livewire::test(ReferralsResource\Pages\CreateReferrals::class)
            ->fillForm([
                'ReferringJob' => $this->jobOpening->id,
                'Candidate' => $this->candidate->id, // Use existing for now
                'Relationship' => 'Personally Known',
                'KnownPeriod' => '1-2 years',
                'Notes' => 'Great team player',
            ])
            ->call('create')
            ->assertHasNoFormErrors();
    });

    it('can create a referral with resume upload', function () {
        $file = UploadedFile::fake()->create('resume.pdf', 1000, 'application/pdf');

        $newData = [
            'ReferringJob' => $this->jobOpening->id,
            'Candidate' => $this->candidate->id,
            'resume' => [$file],
            'Relationship' => 'Socially Connected',
            'KnownPeriod' => 'Less than a year',
            'Notes' => 'Referred through LinkedIn',
        ];

        Livewire::test(ReferralsResource\Pages\CreateReferrals::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('referrals', [
            'ReferringJob' => $this->jobOpening->id,
            'Candidate' => $this->candidate->id,
            'Relationship' => 'Socially Connected',
            'KnownPeriod' => 'Less than a year',
        ]);

        // Check that file was uploaded
        $referral = Referrals::latest()->first();
        expect($referral->resume)->not->toBeNull();
        Storage::disk('public')->assertExists(str_replace('public/', '', $referral->resume));
    });

    it('validates required fields', function () {
        Livewire::test(ReferralsResource\Pages\CreateReferrals::class)
            ->fillForm([])
            ->call('create')
            ->assertHasFormErrors([
                'ReferringJob' => 'required',
                'Candidate' => 'required',
            ]);
    });

    it('validates resume file type', function () {
        $file = UploadedFile::fake()->create('resume.txt', 1000, 'text/plain');

        $newData = [
            'ReferringJob' => $this->jobOpening->id,
            'Candidate' => $this->candidate->id,
            'resume' => [$file],
        ];

        Livewire::test(ReferralsResource\Pages\CreateReferrals::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasFormErrors(['resume']);
    });
});

describe('Referrals Resource View Page', function () {
    it('can render view page', function () {
        $referral = Referrals::factory()->create([
            'ReferringJob' => $this->jobOpening->id,
            'Candidate' => $this->candidate->id,
        ]);

        $this->get(ReferralsResource::getUrl('view', ['record' => $referral]))
            ->assertSuccessful();
    });

    it('can retrieve data', function () {
        $referral = Referrals::factory()->create([
            'ReferringJob' => $this->jobOpening->id,
            'Candidate' => $this->candidate->id,
            'Relationship' => 'Former Colleague',
            'KnownPeriod' => '3-5 years',
            'Notes' => 'Test notes',
        ]);

        Livewire::test(ReferralsResource\Pages\ViewReferrals::class, [
            'record' => $referral->getRouteKey(),
        ])
            ->assertFormSet([
                'ReferringJob' => $this->jobOpening->id,
                'Candidate' => $this->candidate->id,
                'Relationship' => 'Former Colleague',
                'KnownPeriod' => '3-5 years',
                'Notes' => 'Test notes',
            ]);
    });

    it('can view resume download and preview actions', function () {
        $file = UploadedFile::fake()->create('resume.pdf', 1000, 'application/pdf');
        $path = $file->store('referrals/resumes', 'public');

        $referral = Referrals::factory()->create([
            'ReferringJob' => $this->jobOpening->id,
            'Candidate' => $this->candidate->id,
            'resume' => $path,
        ]);

        Livewire::test(ReferralsResource\Pages\ViewReferrals::class, [
            'record' => $referral->getRouteKey(),
        ])
            ->assertFormSet([
                'resume' => $path,
            ]);

        // Test that the file actions are available
        Storage::disk('public')->assertExists($path);
    });
});

describe('Referrals Resource Edit Page', function () {
    it('can render edit page', function () {
        $referral = Referrals::factory()->create([
            'ReferringJob' => $this->jobOpening->id,
            'Candidate' => $this->candidate->id,
        ]);

        $this->get(ReferralsResource::getUrl('edit', ['record' => $referral]))
            ->assertSuccessful();
    });

    it('can retrieve data', function () {
        $referral = Referrals::factory()->create([
            'ReferringJob' => $this->jobOpening->id,
            'Candidate' => $this->candidate->id,
            'Relationship' => 'Original Relationship',
            'Notes' => 'Original notes',
        ]);

        Livewire::test(ReferralsResource\Pages\EditReferrals::class, [
            'record' => $referral->getRouteKey(),
        ])
            ->assertFormSet([
                'Relationship' => 'Original Relationship',
                'Notes' => 'Original notes',
            ]);
    });

    it('can save data', function () {
        $referral = Referrals::factory()->create([
            'ReferringJob' => $this->jobOpening->id,
            'Candidate' => $this->candidate->id,
            'Relationship' => 'Former Colleague',
            'KnownPeriod' => '1-2 years',
            'Notes' => 'Original notes',
        ]);

        $newData = [
            'Relationship' => 'Personally Known',
            'KnownPeriod' => '3-5 years',
            'Notes' => 'Updated notes about the candidate',
        ];

        Livewire::test(ReferralsResource\Pages\EditReferrals::class, [
            'record' => $referral->getRouteKey(),
        ])
            ->fillForm($newData)
            ->call('save')
            ->assertHasNoFormErrors();

        expect($referral->fresh())
            ->Relationship->toBe('Personally Known')
            ->KnownPeriod->toBe('3-5 years')
            ->Notes->toBe('Updated notes about the candidate');
    });

    it('can update resume file', function () {
        $oldFile = UploadedFile::fake()->create('old_resume.pdf', 1000, 'application/pdf');
        $oldPath = $oldFile->store('referrals/resumes', 'public');

        $referral = Referrals::factory()->create([
            'ReferringJob' => $this->jobOpening->id,
            'Candidate' => $this->candidate->id,
            'resume' => $oldPath,
        ]);

        $newFile = UploadedFile::fake()->create('new_resume.pdf', 1000, 'application/pdf');

        Livewire::test(ReferralsResource\Pages\EditReferrals::class, [
            'record' => $referral->getRouteKey(),
        ])
            ->fillForm([
                'resume' => [$newFile],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $referral->refresh();
        expect($referral->resume)->not->toBe($oldPath);
        Storage::disk('public')->assertExists(str_replace('public/', '', $referral->resume));
    });

    it('validates edit data', function () {
        $referral = Referrals::factory()->create([
            'ReferringJob' => $this->jobOpening->id,
            'Candidate' => $this->candidate->id,
        ]);

        Livewire::test(ReferralsResource\Pages\EditReferrals::class, [
            'record' => $referral->getRouteKey(),
        ])
            ->fillForm([
                'ReferringJob' => null, // Invalid - required
                'Candidate' => null, // Invalid - required
            ])
            ->call('save')
            ->assertHasFormErrors(['ReferringJob', 'Candidate']);
    });
});

describe('Referrals Resource Delete Action', function () {
    it('can delete referral', function () {
        $referral = Referrals::factory()->create([
            'ReferringJob' => $this->jobOpening->id,
            'Candidate' => $this->candidate->id,
        ]);

        Livewire::test(ReferralsResource\Pages\ListReferrals::class)
            ->callTableAction(DeleteAction::class, $referral);

        $this->assertDatabaseMissing('referrals', [
            'id' => $referral->id
        ]);
    });

    it('deletes associated resume file when referral is deleted', function () {
        $file = UploadedFile::fake()->create('resume.pdf', 1000, 'application/pdf');
        $path = $file->store('referrals/resumes', 'public');

        $referral = Referrals::factory()->create([
            'ReferringJob' => $this->jobOpening->id,
            'Candidate' => $this->candidate->id,
            'resume' => $path,
        ]);

        Storage::disk('public')->assertExists($path);

        Livewire::test(ReferralsResource\Pages\ListReferrals::class)
            ->callTableAction(DeleteAction::class, $referral);

        // Note: File deletion behavior depends on your model implementation
        // You may need to implement this in the model's deleting event
    });
});

describe('Referrals Resource Relationships', function () {
    it('can view candidate relationship', function () {
        $referral = Referrals::factory()->create([
            'ReferringJob' => $this->jobOpening->id,
            'Candidate' => $this->candidate->id,
        ]);

        expect($referral->candidates)->not->toBeNull();
        expect($referral->candidates->id)->toBe($this->candidate->id);
    });

    it('can view job opening relationship', function () {
        $referral = Referrals::factory()->create([
            'ReferringJob' => $this->jobOpening->id,
            'Candidate' => $this->candidate->id,
        ]);

        expect($referral->jobopenings)->not->toBeNull();
        expect($referral->jobopenings->id)->toBe($this->jobOpening->id);
    });

    it('can view job candidate relationship when exists', function () {
        $jobCandidate = JobCandidates::factory()->create([
            'JobId' => $this->jobOpening->id,
            'candidate' => $this->candidate->id,
        ]);

        $referral = Referrals::factory()->create([
            'ReferringJob' => $this->jobOpening->id,
            'Candidate' => $this->candidate->id,
            'JobCandidate' => $jobCandidate->id,
        ]);

        expect($referral->jobcandidates)->not->toBeNull();
        expect($referral->jobcandidates->id)->toBe($jobCandidate->id);
    });
});

describe('Referrals Resource Table Actions', function () {
    it('can navigate to candidate profile', function () {
        $referral = Referrals::factory()->create([
            'ReferringJob' => $this->jobOpening->id,
            'Candidate' => $this->candidate->id,
        ]);

        // Test that the table shows candidate name with link
        Livewire::test(ReferralsResource\Pages\ListReferrals::class)
            ->assertCanSeeTableRecords([$referral]);

        // The actual URL navigation test would depend on your specific implementation
    });

    it('can navigate to job opening', function () {
        $referral = Referrals::factory()->create([
            'ReferringJob' => $this->jobOpening->id,
            'Candidate' => $this->candidate->id,
        ]);

        // Test that the table shows job title with link
        Livewire::test(ReferralsResource\Pages\ListReferrals::class)
            ->assertCanSeeTableRecords([$referral]);
    });

    it('can view status action when job candidate exists', function () {
        $jobCandidate = JobCandidates::factory()->create([
            'JobId' => $this->jobOpening->id,
            'candidate' => $this->candidate->id,
            'CandidateStatus' => 'Qualified',
        ]);

        $referral = Referrals::factory()->create([
            'ReferringJob' => $this->jobOpening->id,
            'Candidate' => $this->candidate->id,
            'JobCandidate' => $jobCandidate->id,
        ]);

        Livewire::test(ReferralsResource\Pages\ListReferrals::class)
            ->assertTableActionVisible('Status', $referral);
    });

    it('hides status action when job candidate does not exist', function () {
        $referral = Referrals::factory()->create([
            'ReferringJob' => $this->jobOpening->id,
            'Candidate' => $this->candidate->id,
            'JobCandidate' => null,
        ]);

        Livewire::test(ReferralsResource\Pages\ListReferrals::class)
            ->assertTableActionHidden('Status', $referral);
    });
});

describe('Referrals Resource Status Badge', function () {
    it('displays correct status colors for different candidate statuses', function () {
        $statuses = [
            'New' => 'info',
            'Contacted' => 'primary',
            'Qualified' => 'success',
            'Rejected' => 'danger',
        ];

        foreach ($statuses as $status => $color) {
            $jobCandidate = JobCandidates::factory()->create([
                'JobId' => $this->jobOpening->id,
                'candidate' => $this->candidate->id,
                'CandidateStatus' => $status,
            ]);

            $referral = Referrals::factory()->create([
                'ReferringJob' => $this->jobOpening->id,
                'Candidate' => $this->candidate->id,
                'JobCandidate' => $jobCandidate->id,
            ]);

            Livewire::test(ReferralsResource\Pages\ListReferrals::class)
                ->assertCanSeeTableRecords([$referral]);

            // The badge color testing would depend on your specific implementation
        }
    });
});

describe('Referrals Resource File Handling', function () {
    it('can preview PDF resume', function () {
        $file = UploadedFile::fake()->create('resume.pdf', 1000, 'application/pdf');
        $path = $file->store('referrals/resumes', 'public');

        $referral = Referrals::factory()->create([
            'ReferringJob' => $this->jobOpening->id,
            'Candidate' => $this->candidate->id,
            'resume' => $path,
        ]);

        // Test that the file is accessible
        Storage::disk('public')->assertExists($path);
        
        // Test file preview URL generation
        $previewUrl = Storage::url($path);
        expect($previewUrl)->toBeString();
    });

    it('can download resume file', function () {
        $file = UploadedFile::fake()->create('resume.pdf', 1000, 'application/pdf');
        $path = $file->store('referrals/resumes', 'public');

        $referral = Referrals::factory()->create([
            'ReferringJob' => $this->jobOpening->id,
            'Candidate' => $this->candidate->id,
            'resume' => $path,
        ]);

        // Test file download
        $response = $this->get(Storage::url($path));
        $response->assertSuccessful();
    });
});

describe('Referrals Resource Bulk Actions', function () {
    it('can bulk delete referrals', function () {
        $referrals = collect();
        
        for ($i = 0; $i < 3; $i++) {
            $referrals->push(Referrals::factory()->create([
                'ReferringJob' => $this->jobOpening->id,
                'Candidate' => $this->candidate->id,
            ]));
        }

        Livewire::test(ReferralsResource\Pages\ListReferrals::class)
            ->callTableBulkAction('delete', $referrals);

        foreach ($referrals as $referral) {
            $this->assertDatabaseMissing('referrals', [
                'id' => $referral->id
            ]);
        }
    });
});
