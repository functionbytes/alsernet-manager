<?php

namespace App\Policies;

use App\Models\Setting\Media\MediaFile;
use App\Models\User;

class MediaFilePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, MediaFile $mediaFile): bool
    {
        return $user->id === $mediaFile->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MediaFile $mediaFile): bool
    {
        return $user->id === $mediaFile->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MediaFile $mediaFile): bool
    {
        return $user->id === $mediaFile->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, MediaFile $mediaFile): bool
    {
        return $user->id === $mediaFile->user_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, MediaFile $mediaFile): bool
    {
        return $user->id === $mediaFile->user_id;
    }
}
