<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class SingleSessionService
{
    /**
     * Cache key prefix for active user sessions.
     */
    protected const CACHE_PREFIX = 'user_active_session_';

    /**
     * Register a new active session for the user, invalidating any previous session.
     *
     * @param User $user
     * @param string $sessionId
     * @return void
     */
    public function registerSession(User $user, string $sessionId): void
    {
        $lifetimeMinutes = config('session.lifetime', 120);

        // 1. Update Cache store (Redis or File cache)
        Cache::put(self::CACHE_PREFIX . $user->id, $sessionId, now()->addMinutes($lifetimeMinutes));

        // 2. Persist in database as fallback
        $user->current_session_id = $sessionId;
        $user->save();
    }

    /**
     * Verify whether the given session ID matches the registered active session for the user.
     *
     * @param User $user
     * @param string $currentSessionId
     * @return bool
     */
    public function isValidSession(User $user, string $currentSessionId): bool
    {
        // 1. Check Cache store first
        $activeSessionId = Cache::get(self::CACHE_PREFIX . $user->id);

        // 2. Fallback to database column if cache missed
        if (!$activeSessionId) {
            $activeSessionId = $user->current_session_id;

            // Re-populate cache if found in DB
            if ($activeSessionId) {
                Cache::put(self::CACHE_PREFIX . $user->id, $activeSessionId, now()->addMinutes(config('session.lifetime', 120)));
            }
        }

        // If no active session registered yet, allow current session & register it
        if (!$activeSessionId) {
            $this->registerSession($user, $currentSessionId);
            return true;
        }

        return $activeSessionId === $currentSessionId;
    }

    /**
     * Invalidate and remove active session registration for a user.
     *
     * @param User $user
     * @return void
     */
    public function invalidateSession(User $user): void
    {
        Cache::forget(self::CACHE_PREFIX . $user->id);

        $user->current_session_id = null;
        $user->save();
    }

    /**
     * Forcibly invalidate all active sessions and API tokens for a user.
     *
     * @param User $user
     * @return void
     */
    public function forceInvalidateSession(User $user): void
    {
        // 1. Remove from Cache
        Cache::forget(self::CACHE_PREFIX . $user->id);

        // 2. Clear Database Session ID
        $user->current_session_id = null;
        $user->save();

        // 3. Revoke API / Sanctum Tokens if present
        if (method_exists($user, 'tokens')) {
            $user->tokens()->delete();
        }
    }

    /**
     * Keep only the current session active for a user and forcibly terminate all other sessions.
     *
     * @param User $user
     * @param string $currentSessionId
     * @return void
     */
    public function forceInvalidateOtherSessions(User $user, string $currentSessionId): void
    {
        // Re-register current session as the only valid active session
        $this->registerSession($user, $currentSessionId);
    }
}
