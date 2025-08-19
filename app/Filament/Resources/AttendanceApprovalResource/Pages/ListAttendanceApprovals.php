<?php

namespace App\Filament\Resources\AttendanceApprovalResource\Pages;

use App\Filament\Resources\AttendanceApprovalResource;
use App\Models\AttendanceApproval;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListAttendanceApprovals extends ListRecords
{
    protected static string $resource = AttendanceApprovalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // For CreateAction if needed
        ];
    }

    public function getTabs(): array
    {
        return [
            'pending' => ListRecords\Tab::make()
                ->label('Pending')
                ->badge(AttendanceApproval::query()->where('status', 'pending')->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending')),
            'approved' => ListRecords\Tab::make()
                ->label('Approved')
                ->badge(AttendanceApproval::query()->where('status', 'approved')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'approved')),
            'rejected' => ListRecords\Tab::make()
                ->label('Rejected')
                ->badge(AttendanceApproval::query()->where('status', 'rejected')->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'rejected')),
            'all' => ListRecords\Tab::make()
                ->label('All Records')
                ->modifyQueryUsing(fn (Builder $query) => $query),
        ];
    }
}
