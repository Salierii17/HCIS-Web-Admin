{{-- The change is on this first line. We are adding the class "equal-height-widgets" --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 equal-height-widgets">

    {{-- Widget 1 wrapper --}}
    <div>
        @livewire(\App\Filament\Widgets\Reports\AttendanceStatusWidget::class)
    </div>

    {{-- Widget 2 wrapper --}}
    <div>
        @livewire(\App\Filament\Widgets\Reports\DailyAttendanceTrendWidget::class)
    </div>

    {{-- Widget 3 wrapper --}}
    <div class="lg:col-span-2">
        @livewire(\App\Filament\Widgets\Reports\ApprovalStatusFunnelWidget::class)
    </div>

</div>