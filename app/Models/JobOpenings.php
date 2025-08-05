<?php

namespace App\Models;

use Alfa6661\AutoNumber\AutoNumberTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Wildside\Userstamps\Userstamps;

class JobOpenings extends Model
{
    use AutoNumberTrait, HasFactory, SoftDeletes, Userstamps;

    const CREATED_BY = 'CreatedBy';

    const UPDATED_BY = 'ModifiedBy';

    const DELETED_BY = 'DeletedBy';

    protected $fillable = [
        'postingTitle',
        'NumberOfPosition',
        'JobTitle',
        'JobOpeningSystemID',
        'TargetDate',
        'Status',
        'Industry',
        'Salary',
        'Department',
        'HiringManager',
        'AssignedRecruiters',
        'DateOpened',
        'JobType',
        'RequiredSkill',
        'WorkExperience',
        'JobDescription',
        'JobRequirement',
        'JobBenefits',
        'AdditionalNotes',
        'City',
        'Country',
        'State',
        'ZipCode',
        'RemoteJob',
        'published_career_site',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

    public function department(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Departments::class, 'Department', 'id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachments::class, 'attachmentOwner', 'id');
    }

    public function scopeJobStillOpen(Builder $query): void
    {
        $query->where('Status', '=', 'Opened');
    }

    public function scopeFarFromTargetDate(Builder $query): void
    {
        $query->where('TargetDate', '>=', now()->format('d/m/Y'));
    }

    public function scopeRemoteJob(Builder $query): void
    {
        $query->where('RemoteJob', '=', true);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('Status', '!=', 'Closed')
            ->where('TargetDate', '>', now());
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('published_career_site', true);
    }

    public function scopeShouldBeOpened(Builder $query): void
    {
        $query->where('DateOpened', '<=', now())
            ->where('Status', 'New');
    }

    public function scopeShouldBeClosed(Builder $query): void
    {
        $query->where('TargetDate', '<=', now())
            ->where(function($q) {
                $q->where('Status', '!=', 'Closed')
                    ->orWhere('published_career_site', 1);
            });
    }

    /**
     * @return array[]
     */
    public function getAutoNumberOptions(): array
    {
        return [
            'JobOpeningSystemID' => [
                'format' => 'RLR_?_JOB',
                'length' => 5,
            ],
        ];
    }

    // protected $casts = [
    //     'RequiredSkill' => 'array',
    //     'TargetDate' => 'datetime',
    //     'DateOpened' => 'datetime',
    //     'created_at' => 'datetime',
    //     'updated_at' => 'datetime',
    // ];

    protected $casts = [
        'RequiredSkill' => 'array',
        'TargetDate' => 'datetime:Y-m-d H:i:s',
        'DateOpened' => 'datetime:Y-m-d H:i:s',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
}
