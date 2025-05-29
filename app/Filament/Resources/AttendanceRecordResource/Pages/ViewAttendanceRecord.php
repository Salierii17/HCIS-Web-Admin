<?php

namespace App\Filament\Resources\AttendanceRecordResource\Pages;

use App\Filament\Resources\AttendanceRecordResource;
use Filament\Actions;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;
use Filament\Resources\Pages\ViewRecord;

class ViewAttendanceRecord extends ViewRecord
{
    protected static string $resource = AttendanceRecordResource::class;
// D:\Capstone\Coding\Admin\Recruitment\app\Filament\Resources\AttendanceRecordResource\Pages\view-attendance-record.blade.php
    // protected static string $view = 'filament.resources.pages.attendance.viewAttendanceRecord';
    protected static string $view = 'filament.pages.attendance.view-attendance-record-open-street-maps';

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Grid::make(1) // Main grid for info list items
                    ->schema([
                        Components\Section::make('Attendance Details')
                            ->schema([
                                Components\TextEntry::make('employee.name')
                                    ->label('Employee'),
                                Components\TextEntry::make('date')
                                    ->date(),
                                Components\TextEntry::make('clock_in_time')
                                    ->time('H:i'),
                                Components\TextEntry::make('clock_out_time')
                                    ->time('H:i'),
                                Components\TextEntry::make('formattedWorkDuration') // Using the accessor
                                    ->label('Work Duration'),
                                Components\TextEntry::make('locationType.arrangement_type')
                                    ->label('Work Arrangement')
                                    ->badge()
                                    ->color(fn(string $state): string => match (strtolower($state)) {
                                        'wfo' => 'success',
                                        'wfh' => 'info',
                                        default => 'gray',
                                    }),
                                Components\TextEntry::make('status.status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn(string $state): string => match (strtolower($state)) {
                                        'present' => 'success',
                                        'absent' => 'danger',
                                        'on leave' => 'warning',
                                        'holiday' => 'info',
                                        default => 'primary',
                                    }),
                                Components\TextEntry::make('work_hours')
                                    ->label('Work Hours (Decimal)')
                                    ->placeholder('N/A'),
                                Components\TextEntry::make('gps_coordinates')
                                    ->label('GPS Coordinates'),
                                Components\TextEntry::make('notes')
                                    ->columnSpanFull(),
                            ])->columns(2), // Two columns within this section
                    ]),
            ]);
    }
    public function getGpsLocation(): ?array
    {
        return $this->record->gps_coordinates_array; // Using the accessor
    }

}
