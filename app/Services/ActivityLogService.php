<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class ActivityLogService
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function record(
        string $action,
        ?User $user,
        ?Request $request = null,
        ?string $targetType = null,
        ?int $targetId = null,
        array $metadata = [],
    ): void {
        try {
            ActivityLog::create([
                'user_id' => $user?->id,
                'store_id' => $user?->store_id,
                'store_name' => $user?->store_name,
                'role' => $user?->role,
                'action' => $action,
                'target_type' => $targetType,
                'target_id' => $targetId,
                'metadata' => $metadata,
                'ip_address' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
            ]);
        } catch (Throwable $exception) {
            Log::warning('Gagal mencatat activity log.', [
                'action' => $action,
                'user_id' => $user?->id,
                'target_type' => $targetType,
                'target_id' => $targetId,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
