<?php

namespace App\Services\User;

use App\Http\Requests\Core\PasswordUpdateRequest;
use App\Http\Requests\User\UserAvatarRequest;
use App\Repositories\User\Interfaces\NotificationInterface;
use App\Repositories\User\Interfaces\UserInterface;
use App\Services\Core\FileUploadService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileService
{
    public function profile()
    {
        return ['user' => Auth::user()->load('role', 'profile')];
    }

    public function updatePassword(PasswordUpdateRequest $request)
    {
        $update = ['password' => Hash::make($request->new_password)];

        if (app(UserInterface::class)->update($update, Auth::id())) {
            $notification = ['user_id' => Auth::id(), 'data' => __("You just changed your account's password.")];
            app(NotificationInterface::class)->create($notification);

            return [
                SERVICE_RESPONSE_STATUS => true,
                SERVICE_RESPONSE_MESSAGE => __('Password has been changed successfully.')
            ];
        }

        return [
            SERVICE_RESPONSE_STATUS => false,
            SERVICE_RESPONSE_MESSAGE => __('Failed to change password.')
        ];
    }

    public function avatarUpload(UserAvatarRequest $request)
    {
        $uploadedAvatar = app(FileUploadService::class)->upload($request->file('avatar'), config('commonconfig.path_profile_image'), 'avatar', 'user', Auth::id(), 'public', 300, 300);

        if ($uploadedAvatar) {
            $parameters = ['avatar' => $uploadedAvatar];

            if (app(UserInterface::class)->update($parameters, Auth::id())) {
                return [
                    SERVICE_RESPONSE_STATUS => true,
                    SERVICE_RESPONSE_MESSAGE => __('Avatar has been uploaded successfully.')
                ];
            }
        }

        return [
            SERVICE_RESPONSE_STATUS => false,
            SERVICE_RESPONSE_MESSAGE => __('Failed to upload the avatar.')
        ];
    }
}
