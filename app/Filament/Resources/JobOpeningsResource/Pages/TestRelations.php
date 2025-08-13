<?php

namespace App\Filament\Resources\JobOpeningsResource\Pages;

use App\Filament\Resources\JobOpeningsResource;
use Filament\Resources\Pages\Page;

class TestRelations extends Page
{
    protected static string $resource = JobOpeningsResource::class;

    protected static string $view = 'filament.resources.job-openings-resource.pages.test-relations';
}
