<?php

namespace App\Policies;

use App\Models\User;
use App\Models\MockTest;

class MockTestPolicy
{
    public function viewAny(User $user)
    {
        return true;
    }

    public function create(User $user)
    {
        return $user->isReviewer();
    }

    public function update(User $user, MockTest $mockTest)
    {
        if ($user->isLeadReviewer()) {
            return true;
        }

        return $mockTest->created_by === $user->id;
    }

    public function publish(User $user)
    {
        return $user->isReviewer();
    }
}
