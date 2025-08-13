<?php

use App\Filament\Resources\JobOpeningsResource;
use App\Models\Departments;
use App\Models\JobOpenings;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    
    // Create a department for testing
    $this->department = Departments::factory()->create([
        'DepartmentName' => 'Test Department'
    ]);
});

describe('JobOpenings Resource Index Page', function () {
    it('can render index page', function () {
        $this->get(JobOpeningsResource::getUrl('index'))
            ->assertSuccessful();
    });

    it('can list job openings', function () {
        $jobOpenings = JobOpenings::factory()->count(3)->create([
            'Department' => $this->department->id,
        ]);

        Livewire::test(JobOpeningsResource\Pages\ListJobOpenings::class)
            ->assertCanSeeTableRecords($jobOpenings);
    });

    it('can search job openings by posting title', function () {
        $jobOpening = JobOpenings::factory()->create([
            'postingTitle' => 'Senior Developer Position',
            'Department' => $this->department->id,
        ]);

        JobOpenings::factory()->create([
            'postingTitle' => 'Marketing Manager',
            'Department' => $this->department->id,
        ]);

        Livewire::test(JobOpeningsResource\Pages\ListJobOpenings::class)
            ->searchTable('Senior Developer')
            ->assertCanSeeTableRecords([$jobOpening])
            ->assertCanNotSeeTableRecords([JobOpenings::where('postingTitle', 'Marketing Manager')->first()]);
    });

    it('can sort job openings by target date', function () {
        $jobOpenings = JobOpenings::factory()->count(3)->create([
            'Department' => $this->department->id,
            'TargetDate' => now()->addDays(rand(1, 30)),
        ]);

        Livewire::test(JobOpeningsResource\Pages\ListJobOpenings::class)
            ->sortTable('TargetDate')
            ->assertCanSeeTableRecords($jobOpenings, inOrder: true);
    });
});

describe('JobOpenings Resource Create Page', function () {
    it('can render create page', function () {
        $this->get(JobOpeningsResource::getUrl('create'))
            ->assertSuccessful();
    });

    it('can create a job opening', function () {
        $newData = [
            'postingTitle' => 'Senior Software Engineer',
            'NumberOfPosition' => '2',
            'JobTitle' => 'Senior Software Engineer',
            'TargetDate' => now()->addDays(30)->format('Y-m-d H:i:s'),
            'Status' => 'New',
            'Salary' => '80000',
            'Department' => $this->department->id,
            'HiringManager' => $this->user->id,
            'AssignedRecruiters' => $this->user->id,
            'DateOpened' => now()->addDay()->format('Y-m-d H:i:s'),
            'JobType' => 'Permanent',
            'RequiredSkill' => ['PHP', 'Laravel'],
            'WorkExperience' => '3_5years',
            'JobDescription' => 'We are looking for a senior software engineer...',
            'JobRequirement' => 'Bachelor degree in Computer Science...',
            'JobBenefits' => 'Competitive salary, health insurance...',
            'City' => 'Jakarta',
            'Country' => 'Indonesia',
            'State' => 'DKI Jakarta',
            'ZipCode' => '12345',
            'RemoteJob' => false,
        ];

        Livewire::test(JobOpeningsResource\Pages\CreateJobOpenings::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('job_openings', [
            'postingTitle' => 'Senior Software Engineer',
            'NumberOfPosition' => '2',
            'JobTitle' => 'Senior Software Engineer',
            'Status' => 'New',
            'Department' => $this->department->id,
        ]);
    });

    it('validates required fields', function () {
        Livewire::test(JobOpeningsResource\Pages\CreateJobOpenings::class)
            ->fillForm([])
            ->call('create')
            ->assertHasFormErrors([
                'postingTitle' => 'required',
                'NumberOfPosition' => 'required',
                'JobTitle' => 'required',
                'TargetDate' => 'required',
                'Status' => 'required',
                'Department' => 'required',
                'DateOpened' => 'required',
                'JobType' => 'required',
                'RequiredSkill' => 'required',
                'WorkExperience' => 'required',
                'JobDescription' => 'required',
                'City' => 'required',
                'Country' => 'required',
                'State' => 'required',
                'ZipCode' => 'required',
            ]);
    });

    it('validates target date is after date opened', function () {
        $dateOpened = now()->addDay();
        $targetDate = now(); // Before DateOpened

        Livewire::test(JobOpeningsResource\Pages\CreateJobOpenings::class)
            ->fillForm([
                'postingTitle' => 'Test Job',
                'NumberOfPosition' => '1',
                'JobTitle' => 'Test Job',
                'TargetDate' => $targetDate->format('Y-m-d H:i:s'),
                'Status' => 'New',
                'Department' => $this->department->id,
                'DateOpened' => $dateOpened->format('Y-m-d H:i:s'),
                'JobType' => 'Permanent',
                'RequiredSkill' => ['PHP'],
                'WorkExperience' => '1_2years',
                'JobDescription' => 'Test description',
                'City' => 'Jakarta',
                'Country' => 'Indonesia',
                'State' => 'DKI Jakarta',
                'ZipCode' => '12345',
                'RemoteJob' => false,
            ])
            ->call('create')
            ->assertHasFormErrors(['TargetDate']);
    });
});

describe('JobOpenings Resource View Page', function () {
    it('can render view page', function () {
        $jobOpening = JobOpenings::factory()->create([
            'Department' => $this->department->id,
        ]);

        $this->get(JobOpeningsResource::getUrl('view', ['record' => $jobOpening]))
            ->assertSuccessful();
    });

    it('can retrieve data', function () {
        $jobOpening = JobOpenings::factory()->create([
            'postingTitle' => 'Test Job Position',
            'JobTitle' => 'Test Job Position',
            'Department' => $this->department->id,
        ]);

        Livewire::test(JobOpeningsResource\Pages\ViewJobOpenings::class, [
            'record' => $jobOpening->getRouteKey(),
        ])
            ->assertFormSet([
                'postingTitle' => 'Test Job Position',
                'JobTitle' => 'Test Job Position',
            ]);
    });
});

describe('JobOpenings Resource Edit Page', function () {
    it('can render edit page', function () {
        $jobOpening = JobOpenings::factory()->create([
            'Department' => $this->department->id,
        ]);

        $this->get(JobOpeningsResource::getUrl('edit', ['record' => $jobOpening]))
            ->assertSuccessful();
    });

    it('can retrieve data', function () {
        $jobOpening = JobOpenings::factory()->create([
            'postingTitle' => 'Original Title',
            'JobTitle' => 'Original Title',
            'Department' => $this->department->id,
        ]);

        Livewire::test(JobOpeningsResource\Pages\EditJobOpenings::class, [
            'record' => $jobOpening->getRouteKey(),
        ])
            ->assertFormSet([
                'postingTitle' => 'Original Title',
                'JobTitle' => 'Original Title',
            ]);
    });

    it('can save data', function () {
        $jobOpening = JobOpenings::factory()->create([
            'postingTitle' => 'Original Title',
            'JobTitle' => 'Original Title',
            'Department' => $this->department->id,
            'Status' => 'New'
        ]);

        $newData = [
            'postingTitle' => 'Updated Title',
            'JobTitle' => 'Updated Title',
            'Status' => 'Opened',
            'JobType' => 'Contract',
        ];

        Livewire::test(JobOpeningsResource\Pages\EditJobOpenings::class, [
            'record' => $jobOpening->getRouteKey(),
        ])
            ->fillForm($newData)
            ->call('save')
            ->assertHasNoFormErrors();

        expect($jobOpening->fresh())
            ->postingTitle->toBe('Updated Title')
            ->JobTitle->toBe('Updated Title')
            ->Status->toBe('Opened')
            ->JobType->toBe('Contract');
    });

    it('validates edit data', function () {
        $jobOpening = JobOpenings::factory()->create([
            'Department' => $this->department->id,
        ]);

        Livewire::test(JobOpeningsResource\Pages\EditJobOpenings::class, [
            'record' => $jobOpening->getRouteKey(),
        ])
            ->fillForm([
                'postingTitle' => '', // Invalid - required
                'TargetDate' => 'invalid-date' // Invalid date format
            ])
            ->call('save')
            ->assertHasFormErrors(['postingTitle', 'TargetDate']);
    });
});

describe('JobOpenings Resource Delete Action', function () {
    it('can delete job opening', function () {
        $jobOpening = JobOpenings::factory()->create([
            'Department' => $this->department->id,
        ]);

        Livewire::test(JobOpeningsResource\Pages\ListJobOpenings::class)
            ->callTableAction(DeleteAction::class, $jobOpening);

        $this->assertSoftDeleted('job_openings', [
            'id' => $jobOpening->id
        ]);
    });
});

describe('JobOpenings Resource Bulk Actions', function () {
    it('can bulk publish job openings', function () {
        $jobOpenings = JobOpenings::factory()->count(3)->create([
            'Department' => $this->department->id,
            'published_career_site' => false,
        ]);

        Livewire::test(JobOpeningsResource\Pages\ListJobOpenings::class)
            ->callTableBulkAction('published', $jobOpenings);

        foreach ($jobOpenings as $jobOpening) {
            expect($jobOpening->fresh()->published_career_site)->toBeTrue();
        }
    });

    it('can bulk unpublish job openings', function () {
        $jobOpenings = JobOpenings::factory()->count(3)->create([
            'Department' => $this->department->id,
            'published_career_site' => true,
        ]);

        Livewire::test(JobOpeningsResource\Pages\ListJobOpenings::class)
            ->callTableBulkAction('unpublished', $jobOpenings);

        foreach ($jobOpenings as $jobOpening) {
            expect($jobOpening->fresh()->published_career_site)->toBeFalse();
        }
    });

    it('can bulk update status', function () {
        $jobOpenings = JobOpenings::factory()->count(3)->create([
            'Department' => $this->department->id,
            'Status' => 'New',
        ]);

        Livewire::test(JobOpeningsResource\Pages\ListJobOpenings::class)
            ->callTableBulkAction('change_status', $jobOpenings, data: [
                'Status' => 'Opened'
            ]);

        foreach ($jobOpenings as $jobOpening) {
            expect($jobOpening->fresh()->Status)->toBe('Opened');
        }
    });
});

describe('JobOpenings Resource Skills Handling', function () {
    it('can handle array skills correctly', function () {
        $skills = ['PHP', 'Laravel', 'Vue.js', 'MySQL'];
        
        $newData = [
            'postingTitle' => 'Developer Position',
            'NumberOfPosition' => '1',
            'JobTitle' => 'Developer Position',
            'TargetDate' => now()->addDays(30)->format('Y-m-d H:i:s'),
            'Status' => 'New',
            'Department' => $this->department->id,
            'DateOpened' => now()->addDay()->format('Y-m-d H:i:s'),
            'JobType' => 'Permanent',
            'RequiredSkill' => $skills,
            'WorkExperience' => '2_3years',
            'JobDescription' => 'Looking for a skilled developer...',
            'City' => 'Jakarta',
            'Country' => 'Indonesia',
            'State' => 'DKI Jakarta',
            'ZipCode' => '12345',
            'RemoteJob' => false,
        ];

        Livewire::test(JobOpeningsResource\Pages\CreateJobOpenings::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $jobOpening = JobOpenings::latest()->first();
        expect($jobOpening->RequiredSkill)->toBe($skills);
    });
});
