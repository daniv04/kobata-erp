<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Products;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ProductsPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Products');
    }

    public function view(AuthUser $authUser, Products $products): bool
    {
        return $authUser->can('View:Products');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Products');
    }

    public function update(AuthUser $authUser, Products $products): bool
    {
        return $authUser->can('Update:Products');
    }

    public function delete(AuthUser $authUser, Products $products): bool
    {
        return $authUser->can('Delete:Products');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Products');
    }

    public function restore(AuthUser $authUser, Products $products): bool
    {
        return $authUser->can('Restore:Products');
    }

    public function forceDelete(AuthUser $authUser, Products $products): bool
    {
        return $authUser->can('ForceDelete:Products');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Products');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Products');
    }

    public function replicate(AuthUser $authUser, Products $products): bool
    {
        return $authUser->can('Replicate:Products');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Products');
    }
}
