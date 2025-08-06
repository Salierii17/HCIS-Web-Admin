<?php

namespace App\Models;

use Alfa6661\AutoNumber\AutoNumberTrait;
use App\Filament\Enums\AttachmentCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Wildside\Userstamps\Userstamps;

class JobCandidates extends Model
{
    use AutoNumberTrait, HasFactory, Notifiable, SoftDeletes, Userstamps;

    const CREATED_BY = 'CreatedBy';

    const UPDATED_BY = 'ModifiedBy';

    const DELETED_BY = 'DeletedBy';

    protected $fillable = [
        'JobCandidateId',
        'JobId',
        'candidate',
        'mobile',
        'Email',
        'ExperienceInYears',
        'CurrentJobTitle',
        'ExpectedSalary',
        'SkillSet',
        'HighestQualificationHeld',
        'CurrentEmployer',
        'CurrentSalary',
        'Street',
        'City',
        'Country',
        'ZipCode',
        'State',
        'CandidateStatus',
        'CandidateSource',
        'CandidateOwner',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

    protected $casts = [
        'SkillSet' => 'array',
        'interview_time' => 'string', // Store as plain string
    ];

    public function candidateProfile(): BelongsTo
    {
        return $this->belongsTo(Candidates::class, 'candidate', 'id');
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(JobOpenings::class, 'JobId', 'id');
    }

    public function recordOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'CandidateOwner', 'id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachments::class, 'attachmentOwner', 'id')
            ->where('moduleName', 'JobCandidates');
    }

    public function resume(): HasMany
    {
        return $this->attachments()
            ->where('category', AttachmentCategory::Resume->value);
    }

    public function candidateResume(): HasMany
    {
        return $this->hasMany(Attachments::class, 'attachmentOwner', 'candidate')
            ->where('moduleName', 'Candidates')
            ->where('category', AttachmentCategory::Resume->value);
    }

    /**
     * @return array[]
     */
    public function getAutoNumberOptions(): array
    {
        return [
            'JobCandidateId' => [
                'format' => 'RLR_?_JOBCAND', // autonumber format. '?' will be replaced with the generated number.
                'length' => 5, // The number of digits in an autonumber
            ],
        ];
    }

    protected static function booted()
    {
        static::updating(function ($model) {
            if ($model->isDirty('CandidateStatus')) {
                session()->put('status_changed_'.$model->id, true);
                session()->put('email_sent_'.$model->id, false);
            }
        });
    }

    public function getEmailSentAttribute()
    {
        return session()->get('email_sent_'.$this->id, false);
    }

    public function routeNotificationForMail($notification)
    {
        // Use the email from the candidate profile if available,
        // otherwise fall back to the email in JobCandidates
        return $this->candidateProfile->email ?? $this->Email;
    }
}
