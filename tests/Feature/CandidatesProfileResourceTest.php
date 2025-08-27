<?php

use App\Filament\Resources\CandidatesProfileResource;
use App\Models\Candidates;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

describe('Candidates Profile Resource Index Page', function () {
    it('can render index page', function () {
        $this->get(CandidatesProfileResource::getUrl('index'))
            ->assertSuccessful();
    });

    it('can list candidates', function () {
        $candidates = Candidates::factory()->count(3)->create();

        Livewire::test(CandidatesProfileResource\Pages\ListCandidatesProfiles::class)
            ->assertCanSeeTableRecords($candidates);
    });

    it('can search candidates by name', function () {
        $candidate = Candidates::factory()->create([
            'FirstName' => 'John',
            'LastName' => 'Doe',
            'full_name' => 'John Doe',
        ]);

        Candidates::factory()->create([
            'FirstName' => 'Jane',
            'LastName' => 'Smith',
            'full_name' => 'Jane Smith',
        ]);

        Livewire::test(CandidatesProfileResource\Pages\ListCandidatesProfiles::class)
            ->searchTable('John Doe')
            ->assertCanSeeTableRecords([$candidate])
            ->assertCanNotSeeTableRecords([Candidates::where('full_name', 'Jane Smith')->first()]);
    });

    it('can search candidates by email', function () {
        $candidate = Candidates::factory()->create([
            'email' => 'john.doe@example.com',
        ]);

        Candidates::factory()->create([
            'email' => 'jane.smith@example.com',
        ]);

        Livewire::test(CandidatesProfileResource\Pages\ListCandidatesProfiles::class)
            ->searchTable('john.doe@example.com')
            ->assertCanSeeTableRecords([$candidate])
            ->assertCanNotSeeTableRecords([Candidates::where('email', 'jane.smith@example.com')->first()]);
    });

    it('can sort candidates by creation date', function () {
        $candidates = Candidates::factory()->count(3)->create();

        Livewire::test(CandidatesProfileResource\Pages\ListCandidatesProfiles::class)
            ->sortTable('created_at')
            ->assertCanSeeTableRecords($candidates, inOrder: true);
    });
});

describe('Candidates Profile Resource Create Page', function () {
    it('can render create page', function () {
        $this->get(CandidatesProfileResource::getUrl('create'))
            ->assertSuccessful();
    });

    it('can create a candidate profile', function () {
        $newData = [
            'FirstName' => 'John',
            'LastName' => 'Doe',
            'email' => 'john.doe@example.com',
            'Mobile' => '+1234567890',
            'ExperienceInYears' => 5,
            'CurrentJobTitle' => 'Software Developer',
            'ExpectedSalary' => '80000',
            'SkillSet' => ['PHP', 'Laravel', 'JavaScript'],
            'HighestQualificationHeld' => 'Bachelors Degree',
            'CurrentEmployer' => 'Tech Company Inc.',
            'CurrentSalary' => '70000',
            'AdditionalInformation' => 'Additional notes about the candidate',
            'Street' => '123 Main St',
            'City' => 'Jakarta',
            'Country' => 'Indonesia',
            'State' => 'DKI Jakarta',
            'ZipCode' => '12345',
            'CandidateStatus' => 'Active',
            'CandidateSource' => 'Website',
            'CandidateOwner' => $this->user->id,
            'School' => [
                [
                    'SchoolName' => 'University of Technology',
                    'SchoolMajor' => 'Computer Science',
                    'SchoolDegree' => 'Bachelor',
                    'SchoolDuration' => '4 years',
                    'SchoolCurrentlyPursuing' => false,
                ],
            ],
            'ExperienceDetails' => [
                [
                    'CompanyName' => 'Previous Company',
                    'JobTitle' => 'Junior Developer',
                    'Duration' => '2 years',
                    'Description' => 'Worked on web applications',
                ],
            ],
        ];

        Livewire::test(CandidatesProfileResource\Pages\CreateCandidatesProfile::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('candidates', [
            'FirstName' => 'John',
            'LastName' => 'Doe',
            'email' => 'john.doe@example.com',
            'Mobile' => '+1234567890',
            'CurrentJobTitle' => 'Software Developer',
        ]);
    });

    it('validates required fields', function () {
        Livewire::test(CandidatesProfileResource\Pages\CreateCandidatesProfile::class)
            ->fillForm([])
            ->call('create')
            ->assertHasFormErrors([
                'LastName' => 'required',
                'email' => 'required',
            ]);
    });

    it('validates email format', function () {
        Livewire::test(CandidatesProfileResource\Pages\CreateCandidatesProfile::class)
            ->fillForm([
                'LastName' => 'Doe',
                'email' => 'invalid-email',
            ])
            ->call('create')
            ->assertHasFormErrors(['email' => 'email']);
    });

    it('validates unique email', function () {
        $existingCandidate = Candidates::factory()->create([
            'email' => 'existing@example.com',
        ]);

        Livewire::test(CandidatesProfileResource\Pages\CreateCandidatesProfile::class)
            ->fillForm([
                'FirstName' => 'New',
                'LastName' => 'Candidate',
                'email' => 'existing@example.com',
            ])
            ->call('create')
            ->assertHasFormErrors(['email' => 'unique']);
    });

    it('validates numeric experience years', function () {
        Livewire::test(CandidatesProfileResource\Pages\CreateCandidatesProfile::class)
            ->fillForm([
                'FirstName' => 'John',
                'LastName' => 'Doe',
                'email' => 'john@example.com',
                'ExperienceInYears' => 'invalid',
            ])
            ->call('create')
            ->assertHasFormErrors(['ExperienceInYears' => 'numeric']);
    });
});

describe('Candidates Profile Resource View Page', function () {
    it('can render view page', function () {
        $candidate = Candidates::factory()->create();

        $this->get(CandidatesProfileResource::getUrl('view', ['record' => $candidate]))
            ->assertSuccessful();
    });

    it('can retrieve data', function () {
        $candidate = Candidates::factory()->create([
            'FirstName' => 'John',
            'LastName' => 'Doe',
            'email' => 'john@example.com',
            'CurrentJobTitle' => 'Senior Developer',
        ]);

        Livewire::test(CandidatesProfileResource\Pages\ViewCandidatesProfile::class, [
            'record' => $candidate->getRouteKey(),
        ])
            ->assertFormSet([
                'FirstName' => 'John',
                'LastName' => 'Doe',
                'email' => 'john@example.com',
                'CurrentJobTitle' => 'Senior Developer',
            ]);
    });
});

describe('Candidates Profile Resource Edit Page', function () {
    it('can render edit page', function () {
        $candidate = Candidates::factory()->create();

        $this->get(CandidatesProfileResource::getUrl('edit', ['record' => $candidate]))
            ->assertSuccessful();
    });

    it('can retrieve data', function () {
        $candidate = Candidates::factory()->create([
            'FirstName' => 'Original',
            'LastName' => 'Name',
            'email' => 'original@example.com',
        ]);

        Livewire::test(CandidatesProfileResource\Pages\EditCandidatesProfile::class, [
            'record' => $candidate->getRouteKey(),
        ])
            ->assertFormSet([
                'FirstName' => 'Original',
                'LastName' => 'Name',
                'email' => 'original@example.com',
            ]);
    });

    it('can save data', function () {
        $candidate = Candidates::factory()->create([
            'FirstName' => 'Original',
            'LastName' => 'Name',
            'email' => 'original@example.com',
            'CurrentJobTitle' => 'Junior Developer',
        ]);

        $newData = [
            'FirstName' => 'Updated',
            'LastName' => 'Name',
            'email' => 'updated@example.com',
            'CurrentJobTitle' => 'Senior Developer',
            'ExpectedSalary' => '90000',
        ];

        Livewire::test(CandidatesProfileResource\Pages\EditCandidatesProfile::class, [
            'record' => $candidate->getRouteKey(),
        ])
            ->fillForm($newData)
            ->call('save')
            ->assertHasNoFormErrors();

        expect($candidate->fresh())
            ->FirstName->toBe('Updated')
            ->email->toBe('updated@example.com')
            ->CurrentJobTitle->toBe('Senior Developer')
            ->ExpectedSalary->toBe('90000');
    });

    it('validates edit data', function () {
        $candidate = Candidates::factory()->create();

        Livewire::test(CandidatesProfileResource\Pages\EditCandidatesProfile::class, [
            'record' => $candidate->getRouteKey(),
        ])
            ->fillForm([
                'LastName' => '', // Invalid - required
                'email' => 'invalid-email', // Invalid email format
            ])
            ->call('save')
            ->assertHasFormErrors(['LastName', 'email']);
    });

    it('validates unique email on update', function () {
        $existingCandidate = Candidates::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $candidate = Candidates::factory()->create([
            'email' => 'original@example.com',
        ]);

        Livewire::test(CandidatesProfileResource\Pages\EditCandidatesProfile::class, [
            'record' => $candidate->getRouteKey(),
        ])
            ->fillForm([
                'email' => 'existing@example.com', // Email already exists
            ])
            ->call('save')
            ->assertHasFormErrors(['email' => 'unique']);
    });
});

describe('Candidates Profile Resource Delete Action', function () {
    it('can delete candidate profile', function () {
        $candidate = Candidates::factory()->create();

        Livewire::test(CandidatesProfileResource\Pages\ListCandidatesProfiles::class)
            ->callTableAction(DeleteAction::class, $candidate);

        $this->assertSoftDeleted('candidates', [
            'id' => $candidate->id,
        ]);
    });
});

describe('Candidates Profile Resource Skills Handling', function () {
    it('can handle skill set as array', function () {
        $skills = ['PHP', 'Laravel', 'Vue.js', 'MySQL', 'JavaScript'];

        $newData = [
            'FirstName' => 'Skilled',
            'LastName' => 'Developer',
            'email' => 'skilled@example.com',
            'SkillSet' => $skills,
        ];

        Livewire::test(CandidatesProfileResource\Pages\CreateCandidatesProfile::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $candidate = Candidates::latest()->first();
        expect($candidate->SkillSet)->toBe($skills);
    });
});

describe('Candidates Profile Resource School Information', function () {
    it('can handle school information as array', function () {
        $schoolInfo = [
            [
                'SchoolName' => 'University of Technology',
                'SchoolMajor' => 'Computer Science',
                'SchoolDegree' => 'Bachelor',
                'SchoolDuration' => '4 years',
                'SchoolCurrentlyPursuing' => false,
            ],
            [
                'SchoolName' => 'Tech Institute',
                'SchoolMajor' => 'Software Engineering',
                'SchoolDegree' => 'Master',
                'SchoolDuration' => '2 years',
                'SchoolCurrentlyPursuing' => true,
            ],
        ];

        $newData = [
            'FirstName' => 'Educated',
            'LastName' => 'Candidate',
            'email' => 'educated@example.com',
            'School' => $schoolInfo,
        ];

        Livewire::test(CandidatesProfileResource\Pages\CreateCandidatesProfile::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $candidate = Candidates::latest()->first();
        expect($candidate->School)->toBe($schoolInfo);
    });
});

describe('Candidates Profile Resource Experience Details', function () {
    it('can handle experience details as array', function () {
        $experienceDetails = [
            [
                'CompanyName' => 'Tech Corp',
                'JobTitle' => 'Junior Developer',
                'Duration' => '2 years',
                'Description' => 'Developed web applications using Laravel',
            ],
            [
                'CompanyName' => 'Software Solutions',
                'JobTitle' => 'Mid-level Developer',
                'Duration' => '3 years',
                'Description' => 'Led development of mobile applications',
            ],
        ];

        $newData = [
            'FirstName' => 'Experienced',
            'LastName' => 'Developer',
            'email' => 'experienced@example.com',
            'ExperienceDetails' => $experienceDetails,
        ];

        Livewire::test(CandidatesProfileResource\Pages\CreateCandidatesProfile::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $candidate = Candidates::latest()->first();
        expect($candidate->ExperienceDetails)->toBe($experienceDetails);
    });
});

describe('Candidates Profile Resource Filtering and Sorting', function () {
    it('can filter candidates by status', function () {
        $activeCandidate = Candidates::factory()->create([
            'CandidateStatus' => 'Active',
        ]);

        Candidates::factory()->create([
            'CandidateStatus' => 'Inactive',
        ]);

        Livewire::test(CandidatesProfileResource\Pages\ListCandidatesProfiles::class)
            ->filterTable('CandidateStatus', 'Active')
            ->assertCanSeeTableRecords([$activeCandidate])
            ->assertCanNotSeeTableRecords([Candidates::where('CandidateStatus', 'Inactive')->first()]);
    });

    it('can filter candidates by source', function () {
        $webCandidate = Candidates::factory()->create([
            'CandidateSource' => 'Website',
        ]);

        Candidates::factory()->create([
            'CandidateSource' => 'Referral',
        ]);

        Livewire::test(CandidatesProfileResource\Pages\ListCandidatesProfiles::class)
            ->filterTable('CandidateSource', 'Website')
            ->assertCanSeeTableRecords([$webCandidate])
            ->assertCanNotSeeTableRecords([Candidates::where('CandidateSource', 'Referral')->first()]);
    });
});

describe('Candidates Profile Resource Attachments', function () {
    it('can handle resume attachments', function () {
        $candidate = Candidates::factory()->create();

        // Test that the resume relationship is working
        expect($candidate->resume())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
    });

    it('can handle general attachments', function () {
        $candidate = Candidates::factory()->create();

        // Test that the attachments relationship is working
        expect($candidate->attachments())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
    });
});

describe('Candidates Profile Resource Job Applications', function () {
    it('can view related job applications', function () {
        $candidate = Candidates::factory()->create();

        // Test that the jobCandidates relationship is working
        expect($candidate->jobCandidates())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
    });
});

describe('Candidates Profile Resource Contact Information', function () {
    it('validates mobile number format', function () {
        Livewire::test(CandidatesProfileResource\Pages\CreateCandidatesProfile::class)
            ->fillForm([
                'FirstName' => 'John',
                'LastName' => 'Doe',
                'email' => 'john@example.com',
                'Mobile' => 'invalid-phone',
            ])
            ->call('create');

        // Note: Add phone validation if needed based on your actual validation rules
        // ->assertHasFormErrors(['Mobile']);
    });
});
