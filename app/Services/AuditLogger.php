<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    /**
     * Catat log audit ke dalam database.
     *
     * @param string $action (Contoh: CREATE, UPDATE, DELETE, VOID, OVERRIDE_PENALTY)
     * @param string $entity (Contoh: User, Rental, Payment)
     * @param int|string|null $entityId (ID dari entitas yang dipengaruhi)
     * @param string|null $reason (Alasan perubahan jika ada)
     * @param int|null $approvedById (ID Admin yang melakukan override/approve)
     * @return void
     */
    public static function log(string $action, string $entity, $entityId = null, ?string $reason = null, ?int $approvedById = null): void
    {
        try {
            AuditLog::create([
                'user_id' => Auth::id(), // null jika sistem yang melakukan
                'action' => $action,
                'entity' => $entity,
                'entity_id' => $entityId,
                'reason' => $reason,
                'approved_by' => $approvedById,
            ]);
        } catch (\Exception $e) {
            // Log silently to file to not interrupt the main process, but we shouldn't fail.
            \Illuminate\Support\Facades\Log::error('Gagal mencatat audit log: ' . $e->getMessage());
        }
    }
}
