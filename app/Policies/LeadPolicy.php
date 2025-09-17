<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LeadPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any leads.
     */
    public function viewAny(User $user)
    {
        // All authenticated users can view the leads list
        // but the query will be filtered based on their level
        return true;
    }

    /**
     * Determine whether the user can view the lead.
     */
    public function view(User $user, Lead $lead)
    {
        // Users with user_level_id = 1 can only view their own leads
        if ($user->user_level_id == 1) {
            return $lead->user_id === $user->id;
        }
        
        // Other users can view all leads
        return true;
    }

    /**
     * Determine whether the user can create leads.
     */
    public function create(User $user)
    {
        // All users can create leads
        return true;
    }

    /**
     * Determine whether the user can update the lead.
     */
    public function update(User $user, Lead $lead)
    {
        // Users with user_level_id = 1 can only update their own leads
        if ($user->user_level_id == 1) {
            return $lead->user_id === $user->id;
        }
        
        // Other users can update all leads
        return true;
    }

    /**
     * Determine whether the user can delete the lead.
     */
    public function delete(User $user, Lead $lead)
    {
        // Users with user_level_id = 1 can only delete their own leads
        if ($user->user_level_id == 1) {
            return $lead->user_id === $user->id;
        }
        
        // Other users can delete all leads
        return true;
    }
}