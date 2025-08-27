<?php

namespace App\Http\Controllers;

use App\Models\JobOpenings;
use Illuminate\Http\Request;

class JobOpeningsController extends Controller
{
    // public function index()
    // {
    //     return JobOpenings::all();
    // }

    public function index()
    {
        return JobOpenings::active()->published()->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'postingTitle' => ['required'],
            'NumberOfPosition' => ['required'],
            'JobTitle' => ['required'],
            'JobOpeningSystemID' => ['nullable'],
            'TargetDate' => ['required', 'datetime', 'after:DateOpened'],
            'Status' => ['required'],
            'Industry' => ['nullable'],
            'Salary' => ['nullable'],
            'Department' => ['required'],
            'HiringManager' => ['nullable'],
            'AssignedRecruiters' => ['nullable'],
            'DateOpened' => ['required', 'datetime', 'after_or_equal:now'],
            'JobType' => ['required'],
            'RequiredSkill' => ['required'],
            'WorkExperience' => ['required'],
            'JobDescription' => ['required'],
            'City' => ['required'],
            'Country' => ['required'],
            'State' => ['required'],
            'ZipCode' => ['required'],
            'RemoteJob' => ['required'],
            'CreatedBy' => ['required'],
            'ModifiedBy' => ['required'],
        ]);

        return JobOpenings::create($request->validated());
    }

    public function show(JobOpenings $jobOpenings)
    {
        return $jobOpenings;
    }

    public function update(Request $request, JobOpenings $jobOpenings)
    {
        $request->validate([
            'postingTitle' => ['required'],
            'NumberOfPosition' => ['required'],
            'JobTitle' => ['required'],
            'JobOpeningSystemID' => ['nullable'],
            'TargetDate' => ['required', 'datetime', 'after:DateOpened'],
            'Status' => ['required'],
            'Industry' => ['nullable'],
            'Salary' => ['nullable'],
            'Department' => ['required'],
            'HiringManager' => ['nullable'],
            'AssignedRecruiters' => ['nullable'],
            'DateOpened' => ['required', 'datetime', 'after_or_equal:now'],
            'JobType' => ['required'],
            'RequiredSkill' => ['required'],
            'WorkExperience' => ['required'],
            'JobDescription' => ['required'],
            'City' => ['required'],
            'Country' => ['required'],
            'State' => ['required'],
            'ZipCode' => ['required'],
            'RemoteJob' => ['required'],
            'CreatedBy' => ['required'],
            'ModifiedBy' => ['required'],
            'TargetDate' => ['required', 'date', 'after:now'],
        ]);

        $jobOpenings->update($request->validated());

        return $jobOpenings;
    }

    public function destroy(JobOpenings $jobOpenings)
    {
        $jobOpenings->delete();

        return response()->json();
    }
}
