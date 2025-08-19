<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 equal-height-widgets">
    <div>
        @livewire(\App\Filament\Widgets\Reports\RecruitmentFunnelWidget::class)
    </div>
    
    <div>
        @livewire(\App\Filament\Widgets\Reports\JobOpeningsByDepartmentWidget::class)
    </div>
</div>

<style>
    .equal-height-widgets > div {
        display: flex;
        flex-direction: column;
    }
    
    .equal-height-widgets .filament-widget {
        flex: 1;
    }
</style>