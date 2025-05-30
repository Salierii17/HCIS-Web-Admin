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

    // Open Street Map
    protected static string $view = 'filament.pages.attendance.view-attendance-record-open-street-maps';
    // Google Maps API
    // protected static string $view = 'filament.pages.attendance.view-attendance-record';

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
                Components\Grid::make(1)
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
                                Components\TextEntry::make('formattedWorkDuration')
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
                                    ->placeholder('--:--'),
                                Components\TextEntry::make('gps_coordinates')
                                    ->label('GPS Coordinates'),
                                Components\TextEntry::make('notes')
                                    ->columnSpanFull(),
                            ])->columns(2),
                    ]),
            ]);
    }
    public function getGpsLocation(): ?array
    {
        return $this->record->gps_coordinates_array; // Using the accessor
    }

    protected function getViewData(): array
    {
        return array_merge(parent::getViewData(), [
            'location' => $this->getGpsLocation(),
        ]);
    }

    // Optional: Add JavaScript to handle map initialization after Livewire updates
    protected function getFooterWidgets(): array
    {
        return [];
    }

    // Ensure proper loading
    public function mount($record): void
    {
        parent::mount($record);

        // Pre-load GPS data
        $this->getGpsLocation();
    }

}