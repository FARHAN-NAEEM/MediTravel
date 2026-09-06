<?php

namespace App\Services;

use App\Models\AdminActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class AdminActivityLogger
{
    public function log(
        string $action,
        string $description,
        ?User $target = null,
        ?User $actor = null,
        array $metadata = [],
    ): AdminActivityLog {
        /** @var Request|null $request */
        $request = app()->bound('request') ? request() : null;

        return AdminActivityLog::query()->create([
            'actor_id' => $actor?->getKey() ?? auth()->id(),
            'target_user_id' => $target?->getKey(),
            'action' => $action,
            'description' => $description,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'metadata' => $metadata === [] ? null : $metadata,
        ]);
    }
}
