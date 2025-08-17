<?php

use App\Filament\Resources\JobCandidatesResource;
use App\Models\Candidates;
use App\Models\Departments;
use App\Models\JobCandidates;
use App\Models\JobOpenings;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    // Create test data
    $this->department = Departments::factory()->create(['DepartmentName' => 'Test Department']);
    $this->jobOpening = JobOpenings::factory()->create(['Department' => $this->department->id]);
    $this->candidate = Candidates::factory()->create(['email' => 'candidate@example.com']);
});

describe('JobCandidates Resource Index Page', function () {
    it('can render index page', function () {
        $this->get(JobCandidatesResource::getUrl('index'))
            ->assertSuccessful();
    });

    it('can list job candidates', function () {
        $jobCandidates = JobCandidates::factory()->count(3)->create([
            'JobId' => $this->jobOpening->id,
            'candidate' => $this->candidate->id,
        ]);

        Livewire::test(JobCandidatesResource\Pages\ListJobCandidates::class)
            ->assertCanSeeTableRecords($jobCandidates);
    });

    it('can search job candidates by email', function () {
        $jobCandidate = JobCandidates::factory()->create([
            'JobId' => $this->jobOpening->id,
            'candidate' => $this->candidate->id,
            'Email' => 'john.doe@example.com',
        ]);

        JobCandidates::factory()->create([
            'JobId' => $this->jobOpening->id,
            'candidate' => $this->candidate->id,
            'Email' => 'jane.smith@example.com',
        ]);

        Livewire::test(JobCandidatesResource\Pages\ListJobCandidates::class)
            ->searchTable('john.doe@example.com')
            ->assertCanSeeTableRecords([$jobCandidate])
            ->assertCanNotSeeTableRecords([JobCandidates::where('Email', 'jane.smith@example.com')->first()]);
    });

    it('can filter job candidates by status', function () {
        $qualifiedCandidate = JobCandidates::factory()->create([
            'JobId' => $this->jobOpening->id,
            'candidate' => $this->candidate->id,
            'CandidateStatus' => 'Qualified',
        ]);

        JobCandidates::factory()->create([
            'JobId' => $this->jobOpening->id,
            'candidate' => $this->candidate->id,
            'CandidateStatus' => 'Rejected',
        ]);

        Livewire::test(JobCandidatesResource\Pages\ListJobCandidates::class)
            ->filterTable('CandidateStatus', 'Qualified')
            ->assertCanSeeTableRecords([$qualifiedCandidate])
            ->assertCanNotSeeTableRecords([JobCandidates::where('CandidateStatus', 'Rejected')->first()]);
    });
});

describe('JobCandidates Resource Create Page', function () {
    it('can render create page', function () {
        $this->get(JobCandidatesResource::getUrl('create'))
            ->assertSuccessful();
    });

    it('can create a job candidate', function () {
        $newData = [
            'JobId' => $this->jobOpening->id,
            'candidate' => $this->candidate->id,
            'Email' => 'newcandidate@example.com',
            'mobile' => '+1234567890',
            'CandidateStatus' => 'New',
            'CandidateSource' => 'web',
            'CandidateOwner' => $this->user->id,
            'ExperienceInYears' => '3years',
            'CurrentJobTitle' => 'Software Developer',
            'ExpectedSalary' => '75000',
            'HighestQualificationHeld' => 'Bachelors Degree',
            'CurrentEmployer' => 'Tech Company Inc.',
            'CurrentSalary' => '65000',
            'Street' => '123 Main St',
            'City' => 'Jakarta',
            'Country' => 'Indonesia',
            'State' => 'DKI Jakarta',
            'ZipCode' => '12345',
        ];

        Livewire::test(JobCandidatesResource\Pages\CreateJobCandidates::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('job_candidates', [
            'JobId' => $this->jobOpening->id,
            'candidate' => $this->candidate->id,
            'Email' => 'newcandidate@example.com',
            'CandidateStatus' => 'New',
        ]);
    });

    it('validates required fields', function () {
        Livewire::test(JobCandidatesResource\Pages\CreateJobCandidates::class)
            ->fillForm([])
            ->call('create')
            ->assertHasFormErrors([
                'JobId' => 'required',
                'candidate' => 'required',
                'Email' => 'required',
                'CandidateStatus' => 'required',
            ]);
    });

    it('validates email format', function () {
        Livewire::test(JobCandidatesResource\Pages\CreateJobCandidates::class)
            ->fillForm([
                'JobId' => $this->jobOpening->id,
                'candidate' => $this->candidate->id,
                'Email' => 'invalid-email',
                'CandidateStatus' => 'New',
            ])
            ->call('create')
            ->assertHasFormErrors(['Email' => 'email']);
    });
});

describe('JobCandidates Resource View Page', function () {
    it('can render view page', function () {
        $jobCandidate = JobCandidates::factory()->create([
            'JobId' => $this->jobOpening->id,
            'candidate' => $this->candidate->id,
        ]);

        $this->get(JobCandidatesResource::getUrl('view', ['record' => $jobCandidate]))
            ->assertSuccessful();
    });

    it('can retrieve data', function () {
        $jobCandidate = JobCandidates::factory()->create([
            'JobId' => $this->jobOpening->id,
            'candidate' => $this->candidate->id,
            'Email' => 'test@example.com',
            'CandidateStatus' => 'Qualified',
        ]);

        Livewire::test(JobCandidatesResource\Pages\ViewJobCandidates::class, [
            'record' => $jobCandidate->getRouteKey(),
        ])
            ->assertFormSet([
                'Email' => 'test@example.com',
                'CandidateStatus' => 'Qualified',
            ]);
    });
});

describe('JobCandidates Resource Edit Page', function () {
    it('can render edit page', function () {
        $jobCandidate = JobCandidates::factory()->create([
            'JobId' => $this->jobOpening->id,
            'candidate' => $this->candidate->id,
        ]);

        $this->get(JobCandidatesResource::getUrl('edit', ['record' => $jobCandidate]))
            ->assertSuccessful();
    });

    it('can retrieve data', function () {
        $jobCandidate = JobCandidates::factory()->create([
            'JobId' => $this->jobOpening->id,
            'candidate' => $this->candidate->id,
            'Email' => 'original@example.com',
            'CandidateStatus' => 'New',
        ]);

        Livewire::test(JobCandidatesResource\Pages\EditJobCandidates::class, [
            'record' => $jobCandidate->getRouteKey(),
        ])
            ->assertFormSet([
                'Email' => 'original@example.com',
                'CandidateStatus' => 'New',
            ]);
    });

    it('can save data', function () {
        $jobCandidate = JobCandidates::factory()->create([
            'JobId' => $this->jobOpening->id,
            'candidate' => $this->candidate->id,
            'Email' => 'original@example.com',
            'CandidateStatus' => 'New',
        ]);

        $newData = [
            'Email' => 'updated@example.com',
            'CandidateStatus' => 'Qualified',
            'CurrentJobTitle' => 'Senior Developer',
        ];

        Livewire::test(JobCandidatesResource\Pages\EditJobCandidates::class, [
            'record' => $jobCandidate->getRouteKey(),
        ])
            ->fillForm($newData)
            ->call('save')
            ->assertHasNoFormErrors();

        expect($jobCandidate->fresh())
            ->Email->toBe('updated@example.com')
            ->CandidateStatus->toBe('Qualified')
            ->CurrentJobTitle->toBe('Senior Developer');
    });

    it('validates edit data', function () {
        $jobCandidate = JobCandidates::factory()->create([
            'JobId' => $this->jobOpening->id,
            'candidate' => $this->candidate->id,
        ]);

        Livewire::test(JobCandidatesResource\Pages\EditJobCandidates::class, [
            'record' => $jobCandidate->getRouteKey(),
        ])
            ->fillForm([
                'Email' => '', // Invalid - required
                'CandidateStatus' => '', // Invalid - required
            ])
            ->call('save')
            ->assertHasFormErrors(['Email', 'CandidateStatus']);
    });
});

describe('JobCandidates Resource Delete Action', function () {
    it('can delete job candidate', function () {
        $jobCandidate = JobCandidates::factory()->create([
            'JobId' => $this->jobOpening->id,
            'candidate' => $this->candidate->id,
        ]);

        Livewire::test(JobCandidatesResource\Pages\ListJobCandidates::class)
            ->callTableAction(DeleteAction::class, $jobCandidate);

        $this->assertSoftDeleted('job_candidates', [
            'id' => $jobCandidate->id,
        ]);
    });
});

describe('JobCandidates Resource Status Management', function () {
    it('can update candidate status', function () {
        $jobCandidate = JobCandidates::factory()->create([
            'JobId' => $this->jobOpening->id,
            'candidate' => $this->candidate->id,
            'CandidateStatus' => 'New',
        ]);

        Livewire::test(JobCandidatesResource\Pages\EditJobCandidates::class, [
            'record' => $jobCandidate->getRouteKey(),
        ])
            ->fillForm(['CandidateStatus' => 'Interview-Scheduled'])
            ->call('save')
            ->assertHasNoFormErrors();

        expect($jobCandidate->fresh()->CandidateStatus)->toBe('Interview-Scheduled');
    });

    it('can handle status transitions', function () {
        $statuses = [
            'New',
            'Contacted',
            'Qualified',
            'Interview-Scheduled',
            'Interview-to-be-Scheduled',
            'Offer-Made',
            'Hired',
            'Joined',
            'Rejected',
            'Rejected-by-Hiring-Manager',
        ];

        foreach ($statuses as $status) {
            $jobCandidate = JobCandidates::factory()->create([
                'JobId' => $this->jobOpening->id,
                'candidate' => $this->candidate->id,
                'CandidateStatus' => 'New',
            ]);

            Livewire::test(JobCandidatesResource\Pages\EditJobCandidates::class, [
                'record' => $jobCandidate->getRouteKey(),
            ])
                ->fillForm(['CandidateStatus' => $status])
                ->call('save')
                ->assertHasNoFormErrors();

            expect($jobCandidate->fresh()->CandidateStatus)->toBe($status);
        }
    });
});

describe('JobCandidates Resource Skill Set Handling', function () {
    it('can handle skill set as array', function () {
        $skills = ['PHP', 'Laravel', 'JavaScript', 'Vue.js'];

        $newData = [
            'JobId' => $this->jobOpening->id,
            'candidate' => $this->candidate->id,
            'Email' => 'skilled@example.com',
            'CandidateStatus' => 'New',
            'SkillSet' => $skills,
        ];

        Livewire::test(JobCandidatesResource\Pages\CreateJobCandidates::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $jobCandidate = JobCandidates::latest()->first();
        expect($jobCandidate->SkillSet)->toBe($skills);
    });
});

describe('JobCandidates Resource Experience Validation', function () {
    it('validates numeric experience years', function () {
        Livewire::test(JobCandidatesResource\Pages\CreateJobCandidates::class)
            ->fillForm([
                'JobId' => $this->jobOpening->id,
                'candidate' => $this->candidate->id,
                'Email' => 'test@example.com',
                'CandidateStatus' => 'New',
                'ExperienceInYears' => 'invalid',
            ])
            ->call('create')
            ->assertHasFormErrors(['ExperienceInYears']);
    });
});

describe('JobCandidates Resource Add User Action', function () {
    it('can create user for hired candidate', function () {
        $jobCandidate = JobCandidates::factory()->create([
            'JobId' => $this->jobOpening->id,
            'candidate' => $this->candidate->id,
            'CandidateStatus' => 'Joined',
            'Email' => 'hired@example.com',
        ]);

        // Mock the candidate profile relationship
        $this->candidate->update(['full_name' => 'John Doe']);

        Livewire::test(JobCandidatesResource\Pages\ListJobCandidates::class)
            ->callTableAction('addUser', $jobCandidate);

        $this->assertDatabaseHas('users', [
            'email' => 'hired@example.com',
            'name' => 'John Doe',
        ]);
    });

    it('prevents creating duplicate users', function () {
        // Create existing user
        User::factory()->create(['email' => 'existing@example.com']);

        $jobCandidate = JobCandidates::factory()->create([
            'JobId' => $this->jobOpening->id,
            'candidate' => $this->candidate->id,
            'CandidateStatus' => 'Joined',
            'Email' => 'existing@example.com',
        ]);

        Livewire::test(JobCandidatesResource\Pages\ListJobCandidates::class)
            ->callTableAction('addUser', $jobCandidate);

        // Should still have only one user with this email
        $this->assertEquals(1, User::where('email', 'existing@example.com')->count());
    });
});

describe('JobCandidates Resource Bulk Actions', function () {
    it('can bulk create users for hired candidates', function () {
        $jobCandidates = collect();

        for ($i = 0; $i < 3; $i++) {
            $candidate = Candidates::factory()->create([
                'email' => "candidate{$i}@example.com",
                'full_name' => "Candidate {$i}",
            ]);

            $jobCandidates->push(JobCandidates::factory()->create([
                'JobId' => $this->jobOpening->id,
                'candidate' => $candidate->id,
                'CandidateStatus' => 'Joined',
                'Email' => "candidate{$i}@example.com",
            ]));
        }

        Livewire::test(JobCandidatesResource\Pages\ListJobCandidates::class)
            ->callTableBulkAction('addUsers', $jobCandidates);

        for ($i = 0; $i < 3; $i++) {
            $this->assertDatabaseHas('users', [
                'email' => "candidate{$i}@example.com",
                'name' => "Candidate {$i}",
            ]);
        }
    });
});
