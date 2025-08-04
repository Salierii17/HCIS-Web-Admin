<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 equal-height-widgets">

    <div>
        @livewire(\App\Filament\Widgets\Reports\AttendanceStatusWidget::class)
    </div>

    <div>
        @livewire(\App\Filament\Widgets\Reports\DailyAttendanceTrendWidget::class)
    </div>

    <div class="lg:col-span-2">
        @livewire(\App\Filament\Widgets\Reports\ApprovalStatusFunnelWidget::class)
    </div>

</div>