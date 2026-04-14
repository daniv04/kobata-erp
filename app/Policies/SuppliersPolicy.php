<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Suppliers;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class SuppliersPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Suppliers');
    }

    public function view(AuthUser $authUser, Suppliers $suppliers): bool
    {
        return $authUser->can('View:Suppliers');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Suppliers');
    }

    public function update(AuthUser $authUser, Suppliers $suppliers): bool
    {
        return $authUser->can('Update:Suppliers');
    }

    public function delete(AuthUser $authUser, Suppliers $suppliers): bool
    {
        return $authUser->can('Delete:Suppliers');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Suppliers');
    }

    public function restore(AuthUser $authUser, Suppliers $suppliers): bool
    {
        return $authUser->can('Restore:Suppliers');
    }

    public function forceDelete(AuthUser $authUser, Suppliers $suppliers): bool
    {
        return $authUser->can('ForceDelete:Suppliers');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Suppliers');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Suppliers');
    }

    public function replicate(AuthUser $authUser, Suppliers $suppliers): bool
    {
        return $authUser->can('Replicate:Suppliers');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Suppliers');
    }
}
