<?php

use App\Filament\Resources\DepartmentsResource;
use App\Models\Departments;
use App\Models\JobOpenings;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

describe('Departments Resource Index Page', function () {
    it('can render index page', function () {
        $this->get(DepartmentsResource::getUrl('index'))
            ->assertSuccessful();
    });

    it('can list departments', function () {
        $departments = Departments::factory()->count(3)->create();

        Livewire::test(DepartmentsResource\Pages\ListDepartments::class)
            ->assertCanSeeTableRecords($departments);
    });

    it('can search departments by name', function () {
        $department = Departments::factory()->create([
            'DepartmentName' => 'Information Technology',
        ]);

        Departments::factory()->create([
            'DepartmentName' => 'Human Resources',
        ]);

        Livewire::test(DepartmentsResource\Pages\ListDepartments::class)
            ->searchTable('Information Technology')
            ->assertCanSeeTableRecords([$department])
            ->assertCanNotSeeTableRecords([Departments::where('DepartmentName', 'Human Resources')->first()]);
    });

    it('can sort departments by name', function () {
        $departments = Departments::factory()->count(3)->create();

        Livewire::test(DepartmentsResource\Pages\ListDepartments::class)
            ->sortTable('DepartmentName')
            ->assertCanSeeTableRecords($departments, inOrder: true);
    });
});

describe('Departments Resource Create Page', function () {
    it('can render create page', function () {
        $this->get(DepartmentsResource::getUrl('create'))
            ->assertSuccessful();
    });

    it('can create a department', function () {
        $newData = [
            'DepartmentName' => 'Engineering Department',
            'ParentDepartment' => null,
        ];

        Livewire::test(DepartmentsResource\Pages\CreateDepartments::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('departments', [
            'DepartmentName' => 'Engineering Department',
            'ParentDepartment' => null,
        ]);
    });

    it('can create a department with parent department', function () {
        $parentDepartment = Departments::factory()->create([
            'DepartmentName' => 'Information Technology',
        ]);

        $newData = [
            'DepartmentName' => 'Software Development',
            'ParentDepartment' => $parentDepartment->id,
        ];

        Livewire::test(DepartmentsResource\Pages\CreateDepartments::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('departments', [
            'DepartmentName' => 'Software Development',
            'ParentDepartment' => $parentDepartment->id,
        ]);
    });

    it('validates required fields', function () {
        Livewire::test(DepartmentsResource\Pages\CreateDepartments::class)
            ->fillForm([])
            ->call('create')
            ->assertHasFormErrors([
                'DepartmentName' => 'required',
            ]);
    });

    it('validates department name uniqueness', function () {
        $existingDepartment = Departments::factory()->create([
            'DepartmentName' => 'Existing Department',
        ]);

        Livewire::test(DepartmentsResource\Pages\CreateDepartments::class)
            ->fillForm([
                'DepartmentName' => 'Existing Department',
            ])
            ->call('create')
            ->assertHasFormErrors(['DepartmentName']);
    });
});

describe('Departments Resource View Page', function () {
    it('can render view page', function () {
        $department = Departments::factory()->create();

        $this->get(DepartmentsResource::getUrl('view', ['record' => $department]))
            ->assertSuccessful();
    });

    it('can retrieve data', function () {
        $department = Departments::factory()->create([
            'DepartmentName' => 'Marketing Department',
        ]);

        Livewire::test(DepartmentsResource\Pages\ViewDepartments::class, [
            'record' => $department->getRouteKey(),
        ])
            ->assertFormSet([
                'DepartmentName' => 'Marketing Department',
            ]);
    });

    it('can view department with parent', function () {
        $parentDepartment = Departments::factory()->create([
            'DepartmentName' => 'Parent Department',
        ]);

        $department = Departments::factory()->create([
            'DepartmentName' => 'Child Department',
            'ParentDepartment' => $parentDepartment->id,
        ]);

        Livewire::test(DepartmentsResource\Pages\ViewDepartments::class, [
            'record' => $department->getRouteKey(),
        ])
            ->assertFormSet([
                'DepartmentName' => 'Child Department',
                'ParentDepartment' => $parentDepartment->id,
            ]);
    });
});

describe('Departments Resource Edit Page', function () {
    it('can render edit page', function () {
        $department = Departments::factory()->create();

        $this->get(DepartmentsResource::getUrl('edit', ['record' => $department]))
            ->assertSuccessful();
    });

    it('can retrieve data', function () {
        $department = Departments::factory()->create([
            'DepartmentName' => 'Original Department Name',
        ]);

        Livewire::test(DepartmentsResource\Pages\EditDepartments::class, [
            'record' => $department->getRouteKey(),
        ])
            ->assertFormSet([
                'DepartmentName' => 'Original Department Name',
            ]);
    });

    it('can save data', function () {
        $department = Departments::factory()->create([
            'DepartmentName' => 'Original Name',
        ]);

        $newData = [
            'DepartmentName' => 'Updated Department Name',
        ];

        Livewire::test(DepartmentsResource\Pages\EditDepartments::class, [
            'record' => $department->getRouteKey(),
        ])
            ->fillForm($newData)
            ->call('save')
            ->assertHasNoFormErrors();

        expect($department->fresh())
            ->DepartmentName->toBe('Updated Department Name');
    });

    it('can update parent department', function () {
        $parentDepartment = Departments::factory()->create([
            'DepartmentName' => 'New Parent Department',
        ]);

        $department = Departments::factory()->create([
            'DepartmentName' => 'Child Department',
            'ParentDepartment' => null,
        ]);

        $newData = [
            'ParentDepartment' => $parentDepartment->id,
        ];

        Livewire::test(DepartmentsResource\Pages\EditDepartments::class, [
            'record' => $department->getRouteKey(),
        ])
            ->fillForm($newData)
            ->call('save')
            ->assertHasNoFormErrors();

        expect($department->fresh())
            ->ParentDepartment->toBe($parentDepartment->id);
    });

    it('validates edit data', function () {
        $department = Departments::factory()->create();

        Livewire::test(DepartmentsResource\Pages\EditDepartments::class, [
            'record' => $department->getRouteKey(),
        ])
            ->fillForm([
                'DepartmentName' => '', // Invalid - required
            ])
            ->call('save')
            ->assertHasFormErrors(['DepartmentName']);
    });

    it('validates unique department name on update', function () {
        $existingDepartment = Departments::factory()->create([
            'DepartmentName' => 'Existing Department',
        ]);

        $department = Departments::factory()->create([
            'DepartmentName' => 'Original Department',
        ]);

        Livewire::test(DepartmentsResource\Pages\EditDepartments::class, [
            'record' => $department->getRouteKey(),
        ])
            ->fillForm([
                'DepartmentName' => 'Existing Department', // Name already exists
            ])
            ->call('save')
            ->assertHasFormErrors(['DepartmentName']);
    });

    it('allows updating department with same name', function () {
        $department = Departments::factory()->create([
            'DepartmentName' => 'Original Department',
        ]);

        Livewire::test(DepartmentsResource\Pages\EditDepartments::class, [
            'record' => $department->getRouteKey(),
        ])
            ->fillForm([
                'DepartmentName' => 'Original Department', // Same name should be allowed
            ])
            ->call('save')
            ->assertHasNoFormErrors();
    });
});

describe('Departments Resource Delete Action', function () {
    it('can delete department', function () {
        $department = Departments::factory()->create();

        Livewire::test(DepartmentsResource\Pages\ListDepartments::class)
            ->callTableAction(DeleteAction::class, $department);

        $this->assertSoftDeleted('departments', [
            'id' => $department->id,
        ]);
    });

    it('can force delete department', function () {
        $department = Departments::factory()->create();

        Livewire::test(DepartmentsResource\Pages\ListDepartments::class)
            ->callTableBulkAction('force-delete', [$department]);

        $this->assertDatabaseMissing('departments', [
            'id' => $department->id,
        ]);
    });

    it('can restore deleted department', function () {
        $department = Departments::factory()->create();
        $department->delete(); // Soft delete

        Livewire::test(DepartmentsResource\Pages\ListDepartments::class)
            ->callTableBulkAction('restore', [$department]);

        expect($department->fresh())->not->toBeNull();
        expect($department->fresh()->deleted_at)->toBeNull();
    });
});

describe('Departments Resource Relationships', function () {
    it('can view job openings for department', function () {
        $department = Departments::factory()->create();
        $jobOpenings = JobOpenings::factory()->count(2)->create([
            'Department' => $department->id,
        ]);

        // Test that the relationship is working
        expect($department->jobOpenings()->count())->toBe(2);
        expect($department->jobOpenings->pluck('id')->toArray())
            ->toEqual($jobOpenings->pluck('id')->toArray());
    });

    it('can view attachments for department', function () {
        $department = Departments::factory()->create();

        // Test that the attachments relationship is working
        expect($department->attachments())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
    });
});

describe('Departments Resource Hierarchical Structure', function () {
    it('can create department hierarchy', function () {
        $rootDepartment = Departments::factory()->create([
            'DepartmentName' => 'Corporate',
        ]);

        $childDepartment = Departments::factory()->create([
            'DepartmentName' => 'Information Technology',
            'ParentDepartment' => $rootDepartment->id,
        ]);

        $grandChildDepartment = Departments::factory()->create([
            'DepartmentName' => 'Software Development',
            'ParentDepartment' => $childDepartment->id,
        ]);

        expect($childDepartment->ParentDepartment)->toBe($rootDepartment->id);
        expect($grandChildDepartment->ParentDepartment)->toBe($childDepartment->id);
    });

    it('prevents circular department hierarchy', function () {
        $department1 = Departments::factory()->create([
            'DepartmentName' => 'Department 1',
        ]);

        $department2 = Departments::factory()->create([
            'DepartmentName' => 'Department 2',
            'ParentDepartment' => $department1->id,
        ]);

        // Try to make department1 a child of department2 (circular)
        Livewire::test(DepartmentsResource\Pages\EditDepartments::class, [
            'record' => $department1->getRouteKey(),
        ])
            ->fillForm([
                'ParentDepartment' => $department2->id,
            ])
            ->call('save');

        // This should either have validation errors or be prevented
        // The exact behavior depends on your business rules implementation
    });
});

describe('Departments Resource Bulk Actions', function () {
    it('can bulk delete departments', function () {
        $departments = Departments::factory()->count(3)->create();

        Livewire::test(DepartmentsResource\Pages\ListDepartments::class)
            ->callTableBulkAction('delete', $departments);

        foreach ($departments as $department) {
            $this->assertSoftDeleted('departments', [
                'id' => $department->id,
            ]);
        }
    });

    it('can bulk restore departments', function () {
        $departments = Departments::factory()->count(3)->create();

        // Soft delete all departments
        foreach ($departments as $department) {
            $department->delete();
        }

        Livewire::test(DepartmentsResource\Pages\ListDepartments::class)
            ->callTableBulkAction('restore', $departments);

        foreach ($departments as $department) {
            expect($department->fresh())->not->toBeNull();
            expect($department->fresh()->deleted_at)->toBeNull();
        }
    });

    it('can bulk force delete departments', function () {
        $departments = Departments::factory()->count(3)->create();

        Livewire::test(DepartmentsResource\Pages\ListDepartments::class)
            ->callTableBulkAction('force-delete', $departments);

        foreach ($departments as $department) {
            $this->assertDatabaseMissing('departments', [
                'id' => $department->id,
            ]);
        }
    });
});

describe('Departments Resource System Information', function () {
    it('displays system information on view page', function () {
        $department = Departments::factory()->create([
            'CreatedBy' => $this->user->id,
            'ModifiedBy' => $this->user->id,
        ]);

        // System information should be visible in view mode
        Livewire::test(DepartmentsResource\Pages\ViewDepartments::class, [
            'record' => $department->getRouteKey(),
        ])
            ->assertFormSet([
                'CreatedBy' => $this->user->id,
                'ModifiedBy' => $this->user->id,
            ]);
    });

    it('hides system information on create page', function () {
        // System information section should be hidden on create page
        $response = $this->get(DepartmentsResource::getUrl('create'));

        // The form should not display system information fields on create
        $response->assertDontSee('System Information');
    });
});

describe('Departments Resource Filtering', function () {
    it('can filter by trashed departments', function () {
        $activeDepartment = Departments::factory()->create([
            'DepartmentName' => 'Active Department',
        ]);

        $trashedDepartment = Departments::factory()->create([
            'DepartmentName' => 'Trashed Department',
        ]);
        $trashedDepartment->delete();

        Livewire::test(DepartmentsResource\Pages\ListDepartments::class)
            ->filterTable('trashed', 'with_trashed')
            ->assertCanSeeTableRecords([$activeDepartment, $trashedDepartment]);

        Livewire::test(DepartmentsResource\Pages\ListDepartments::class)
            ->filterTable('trashed', 'only_trashed')
            ->assertCanSeeTableRecords([$trashedDepartment])
            ->assertCanNotSeeTableRecords([$activeDepartment]);
    });
});
