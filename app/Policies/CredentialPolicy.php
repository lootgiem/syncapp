<?php

namespace App\Policies;

use App\Models\Credential;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class CredentialPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @return mixed
     */
    public function viewAny()
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Credential $credential
     * @return mixed
     */
    public function view(User $user, Credential $credential)
    {
        return $user->id == $credential->user_id
            ? Response::allow()
            : Response::deny('You do not own this credential.');
    }

    /**
     * Determine whether the user can create models.
     *
     * @return mixed
     */
    public function create()
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Credential $credential
     * @return mixed
     */
    public function update(User $user, Credential $credential)
    {
        return $user->id == $credential->user_id
            ? Response::allow()
            : Response::deny('You do not own this credential.');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Credential $credential
     * @return mixed
     */
    public function delete(User $user, Credential $credential)
    {
        return $user->id == $credential->user_id
            ? Response::allow()
            : Response::deny('You do not own this credential.');
    }
}
