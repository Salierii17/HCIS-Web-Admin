<?php

namespace App\Filament\Resources\JobCandidatesResource\Pages;

use App\Filament\Resources\JobCandidatesResource;
use App\Models\User;
use App\Notifications\User\InviteNewSystemUserNotification;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class EditJobCandidates extends EditRecord
{
    protected static string $resource = JobCandidatesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    // protected function afterSave(): void
    // {
    //     // Check if status was changed to "Hired"
    //     if ($this->record->wasChanged('CandidateStatus') && $this->record->CandidateStatus === 'Hired') {
    //         // Check if user already exists with this email
    //         $existingUser = User::where('email', $this->record->Email)->first();

    //         if ($existingUser) {
    //             Notification::make()
    //                 ->title('User already exists')
    //                 ->body('A user with this email already exists in the system.')
    //                 ->danger()
    //                 ->send();
    //             return;
    //         }

    //         // Create new user
    //         $user = User::create([
    //             'name' => $this->record->candidateProfile->full_name,
    //             'email' => $this->record->Email,
    //             'password' => Hash::make('password'), // default password
    //             'invitation_id' => Str::uuid(),
    //             'sent_at' => now(),
    //         ]);

    //         // Assign Standard role
    //         $standardRole = Role::where('name', 'Standard')->first();
    //         if ($standardRole) {
    //             $user->assignRole($standardRole);
    //         }

    //         // Send invitation
    //         $link = URL::signedRoute('system-user.invite', ['id' => $user->invitation_id]);
    //         $user->notify(new InviteNewSystemUserNotification($user, $link));

    //         Notification::make()
    //             ->title('User created and invited')
    //             ->body('The user has been created and an invitation has been sent.')
    //             ->success()
    //             ->send();
    //     }
    // }

    public function getRelationManagers(): array
    {
        return [];
    }
}
