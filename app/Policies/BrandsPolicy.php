<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Brands;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class BrandsPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Brands');
    }

    public function view(AuthUser $authUser, Brands $brands): bool
    {
        return $authUser->can('View:Brands');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Brands');
    }

    public function update(AuthUser $authUser, Brands $brands): bool
    {
        return $authUser->can('Update:Brands');
    }

    public function delete(AuthUser $authUser, Brands $brands): bool
    {
        return $authUser->can('Delete:Brands');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Brands');
    }

    public function restore(AuthUser $authUser, Brands $brands): bool
    {
        return $authUser->can('Restore:Brands');
    }

    public function forceDelete(AuthUser $authUser, Brands $brands): bool
    {
        return $authUser->can('ForceDelete:Brands');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Brands');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Brands');
    }

    public function replicate(AuthUser $authUser, Brands $brands): bool
    {
        return $authUser->can('Replicate:Brands');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Brands');
    }
}
