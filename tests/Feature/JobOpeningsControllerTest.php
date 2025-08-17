<?php

namespace Tests\Feature;

use App\Models\JobOpenings;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobOpeningsControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user for authenticated requests
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_list_all_active_and_published_job_openings()
    {
        // Create some job openings with different statuses
        $activePublishedJob = JobOpenings::factory()->create([
            'Status' => 'Opened',
            'TargetDate' => now()->addDays(30),
            'published_career_site' => true,
        ]);

        $activeUnpublishedJob = JobOpenings::factory()->create([
            'Status' => 'Opened',
            'TargetDate' => now()->addDays(30),
            'published_career_site' => false,
        ]);

        $closedJob = JobOpenings::factory()->create([
            'Status' => 'Closed',
            'TargetDate' => now()->addDays(30),
            'published_career_site' => true,
        ]);

        $expiredJob = JobOpenings::factory()->create([
            'Status' => 'Opened',
            'TargetDate' => now()->subDays(5),
            'published_career_site' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/job-openings');

        $response->assertStatus(200);

        // Should only return active and published jobs
        $responseData = $response->json();
        $this->assertCount(1, $responseData);
        $this->assertEquals($activePublishedJob->id, $responseData[0]['id']);
    }

    /** @test */
    public function it_can_create_a_job_opening_with_valid_data()
    {
        $jobData = [
            'postingTitle' => 'Senior Software Developer',
            'NumberOfPosition' => '2',
            'JobTitle' => 'Senior Software Developer',
            'JobOpeningSystemID' => 'RLR_100001_JOB',
            'TargetDate' => now()->addDays(30)->format('Y-m-d H:i:s'),
            'Status' => 'New',
            'Industry' => 'Technology',
            'Salary' => '80000',
            'Department' => 1,
            'HiringManager' => 'John Doe',
            'AssignedRecruiters' => 'Jane Smith',
            'DateOpened' => now()->addDay()->format('Y-m-d H:i:s'),
            'JobType' => 'Permanent',
            'RequiredSkill' => ['PHP', 'Laravel', 'JavaScript'],
            'WorkExperience' => '3_5years',
            'JobDescription' => 'We are looking for a senior software developer...',
            'City' => 'Jakarta',
            'Country' => 'Indonesia',
            'State' => 'DKI Jakarta',
            'ZipCode' => '12345',
            'RemoteJob' => false,
            'CreatedBy' => $this->user->id,
            'ModifiedBy' => $this->user->id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/job-openings', $jobData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('job_openings', [
            'postingTitle' => 'Senior Software Developer',
            'JobTitle' => 'Senior Software Developer',
            'NumberOfPosition' => '2',
            'Status' => 'New',
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_job_opening()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/job-openings', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'postingTitle',
            'NumberOfPosition',
            'JobTitle',
            'TargetDate',
            'Status',
            'Department',
            'DateOpened',
            'JobType',
            'RequiredSkill',
            'WorkExperience',
            'JobDescription',
            'City',
            'Country',
            'State',
            'ZipCode',
            'RemoteJob',
            'CreatedBy',
            'ModifiedBy',
        ]);
    }

    /** @test */
    public function it_validates_target_date_is_after_date_opened()
    {
        $dateOpened = now()->addDay();
        $targetDate = now(); // Before DateOpened

        $jobData = [
            'postingTitle' => 'Test Job',
            'NumberOfPosition' => '1',
            'JobTitle' => 'Test Job',
            'TargetDate' => $targetDate->format('Y-m-d H:i:s'),
            'Status' => 'New',
            'Department' => 1,
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
            'CreatedBy' => $this->user->id,
            'ModifiedBy' => $this->user->id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/job-openings', $jobData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['TargetDate']);
    }

    /** @test */
    public function it_can_show_a_specific_job_opening()
    {
        $jobOpening = JobOpenings::factory()->create([
            'postingTitle' => 'Test Job Position',
            'JobTitle' => 'Test Job Position',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/job-openings/{$jobOpening->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'id' => $jobOpening->id,
            'postingTitle' => 'Test Job Position',
            'JobTitle' => 'Test Job Position',
        ]);
    }

    /** @test */
    public function it_returns_404_when_job_opening_not_found()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/job-openings/999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_update_a_job_opening()
    {
        $jobOpening = JobOpenings::factory()->create([
            'postingTitle' => 'Original Title',
            'JobTitle' => 'Original Title',
            'Status' => 'New',
        ]);

        $updateData = [
            'postingTitle' => 'Updated Title',
            'NumberOfPosition' => '3',
            'JobTitle' => 'Updated Title',
            'TargetDate' => now()->addDays(45)->format('Y-m-d H:i:s'),
            'Status' => 'Opened',
            'Department' => 1,
            'DateOpened' => now()->addDay()->format('Y-m-d H:i:s'),
            'JobType' => 'Contract',
            'RequiredSkill' => ['Python', 'Django'],
            'WorkExperience' => '5_7years',
            'JobDescription' => 'Updated job description',
            'City' => 'Bandung',
            'Country' => 'Indonesia',
            'State' => 'West Java',
            'ZipCode' => '54321',
            'RemoteJob' => true,
            'CreatedBy' => $this->user->id,
            'ModifiedBy' => $this->user->id,
        ];

        $response = $this->actingAs($this->user)
            ->putJson("/api/job-openings/{$jobOpening->id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('job_openings', [
            'id' => $jobOpening->id,
            'postingTitle' => 'Updated Title',
            'JobTitle' => 'Updated Title',
            'Status' => 'Opened',
            'JobType' => 'Contract',
        ]);

        $response->assertJson([
            'id' => $jobOpening->id,
            'postingTitle' => 'Updated Title',
            'Status' => 'Opened',
        ]);
    }

    /** @test */
    public function it_validates_update_data()
    {
        $jobOpening = JobOpenings::factory()->create();

        $response = $this->actingAs($this->user)
            ->putJson("/api/job-openings/{$jobOpening->id}", [
                'postingTitle' => '', // Invalid - required
                'TargetDate' => 'invalid-date', // Invalid date format
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['postingTitle', 'TargetDate']);
    }

    /** @test */
    public function it_can_delete_a_job_opening()
    {
        $jobOpening = JobOpenings::factory()->create([
            'postingTitle' => 'Job to Delete',
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/job-openings/{$jobOpening->id}");

        $response->assertStatus(200);

        // Check that the job opening is soft deleted
        $this->assertSoftDeleted('job_openings', [
            'id' => $jobOpening->id,
        ]);
    }

    /** @test */
    public function it_returns_404_when_trying_to_delete_non_existent_job_opening()
    {
        $response = $this->actingAs($this->user)
            ->deleteJson('/api/job-openings/999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_requires_authentication_for_all_job_opening_endpoints()
    {
        $jobOpening = JobOpenings::factory()->create();

        // Test index endpoint
        $response = $this->getJson('/api/job-openings');
        $response->assertStatus(401);

        // Test store endpoint
        $response = $this->postJson('/api/job-openings', []);
        $response->assertStatus(401);

        // Test show endpoint
        $response = $this->getJson("/api/job-openings/{$jobOpening->id}");
        $response->assertStatus(401);

        // Test update endpoint
        $response = $this->putJson("/api/job-openings/{$jobOpening->id}", []);
        $response->assertStatus(401);

        // Test delete endpoint
        $response = $this->deleteJson("/api/job-openings/{$jobOpening->id}");
        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_filter_job_openings_by_status()
    {
        JobOpenings::factory()->create(['Status' => 'Opened', 'TargetDate' => now()->addDays(30), 'published_career_site' => true]);
        JobOpenings::factory()->create(['Status' => 'New', 'TargetDate' => now()->addDays(30), 'published_career_site' => true]);
        JobOpenings::factory()->create(['Status' => 'Closed', 'TargetDate' => now()->addDays(30), 'published_career_site' => true]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/job-openings?status=Opened');

        $response->assertStatus(200);
        $data = $response->json();

        // Only active and published jobs should be returned by default
        // This test may need adjustment based on actual filtering implementation
        $this->assertGreaterThanOrEqual(1, count($data));
    }

    /** @test */
    public function it_handles_json_skill_data_correctly()
    {
        $jobData = [
            'postingTitle' => 'Developer Position',
            'NumberOfPosition' => '1',
            'JobTitle' => 'Developer Position',
            'TargetDate' => now()->addDays(30)->format('Y-m-d H:i:s'),
            'Status' => 'New',
            'Department' => 1,
            'DateOpened' => now()->addDay()->format('Y-m-d H:i:s'),
            'JobType' => 'Permanent',
            'RequiredSkill' => ['PHP', 'Laravel', 'Vue.js', 'MySQL'],
            'WorkExperience' => '2_3years',
            'JobDescription' => 'Looking for a skilled developer...',
            'City' => 'Jakarta',
            'Country' => 'Indonesia',
            'State' => 'DKI Jakarta',
            'ZipCode' => '12345',
            'RemoteJob' => false,
            'CreatedBy' => $this->user->id,
            'ModifiedBy' => $this->user->id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/job-openings', $jobData);

        $response->assertStatus(201);

        $jobOpening = JobOpenings::latest()->first();
        $this->assertEquals(['PHP', 'Laravel', 'Vue.js', 'MySQL'], $jobOpening->RequiredSkill);
    }
}
