<?php

namespace App\Filament\Resources\AttendanceRecordResource\Pages;

use App\Filament\Resources\AttendanceRecordResource;
use App\Models\Attendance;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListAttendanceRecords extends ListRecords
{
    protected static string $resource = AttendanceRecordResource::class;

    public function getTabs(): array
    {
        return [
            'flagged_for_review' => ListRecords\Tab::make('Flagged for Review')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('approval_status', 'Flagged for Review'))
                ->badge(Attendance::where('approval_status', 'Flagged for Review')->count())
                ->badgeColor('danger'),

            'pending_approval' => ListRecords\Tab::make('Pending Approval')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('approval_status', 'Pending Approval'))
                ->badge(Attendance::where('approval_status', 'Pending Approval')->count())
                ->badgeColor('warning'),

            'incomplete' => ListRecords\Tab::make('Incomplete')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('approval_status', 'Incomplete'))
                ->badge(Attendance::where('approval_status', 'Incomplete')->count())
                ->badgeColor('gray'),

            'all' => ListRecords\Tab::make('All Records'),

            'verified' => ListRecords\Tab::make('Verified')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('approval_status', 'Verified')),
        ];
    }
}
