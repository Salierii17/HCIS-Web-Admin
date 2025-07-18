<?php

namespace App\Filament\Resources\AttendanceRecordResource\Pages;

use App\Filament\Resources\AttendanceRecordResource;
use Filament\Actions;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use App\Models\Attendance;
use Illuminate\Database\Eloquent\Builder; // Make sure to import the Builder

class ListAttendanceRecords extends ListRecords
{
    protected static string $resource = AttendanceRecordResource::class;

    // protected function getActions(): array
    // {
    //     return [
    //         CreateAction::make()
    //             ->icon('heroicon-m-plus-small'),
    //     ];
    // }

    public function getTabs(): array
    {
        return [
            'all' => ListRecords\Tab::make(),
            
            'flagged_for_review' => ListRecords\Tab::make('Flagged for Review')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('approval_status', 'Flagged for Review'))
                ->badge(Attendance::where('approval_status', 'Flagged for Review')->count())
                ->badgeColor('danger'),

            'pending_approval' => ListRecords\Tab::make('Pending Approval')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('approval_status', 'Pending Approval'))
                ->badge(Attendance::where('approval_status', 'Pending Approval')->count())
                ->badgeColor('warning'),

            'verified' => ListRecords\Tab::make('Verified')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('approval_status', 'Verified')),
            
            'incomplete' => ListRecords\Tab::make('Incomplete')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('approval_status', 'Incomplete')),
        ];
    }

}
