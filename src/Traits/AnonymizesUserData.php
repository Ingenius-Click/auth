<?php

namespace Ingenius\Auth\Traits;

use Illuminate\Support\Facades\DB;
use Ingenius\Core\Services\PackageHookManager;

trait AnonymizesUserData
{
    /**
     * Anonymize user data for GDPR compliance.
     * This method:
     * - Anonymizes personal information (name, email)
     * - Deletes user profile
     * - Dispatches hooks for other packages to clean up their data
     * - Preserves orders, invoices, and shipments (business records)
     *
     * @param string|null $reason Optional reason for deletion
     * @return void
     */
    public function anonymizeUserData(?string $reason = null): void
    {
        DB::transaction(function () use ($reason) {
            $hookManager = app(PackageHookManager::class);

            // 1. Delete profile data (polymorphic relationship)
            if ($this->profile) {
                $this->profile->delete();
            }

            // 2. Execute hook to let other packages clean up their user-related data
            // Shipment package will listen to this hook to delete addresses and beneficiaries
            // ShopCart package will listen to delete cart items
            $hookManager->execute('user.before_anonymize', null, [
                'user_id' => $this->id,
                'user_class' => get_class($this),
            ]);

            // 3. Anonymize user personal data
            $anonymousId = 'deleted_user_' . $this->id . '_' . time();

            $this->updateQuietly([
                'name' => 'Deleted User',
                'email' => $anonymousId . '@deleted.local',
                'email_verified_at' => null,
                'password' => bcrypt(bin2hex(random_bytes(32))), // Random unrecoverable password
                'remember_token' => null,
                'anonymized_at' => now(),
                'deletion_reason' => $reason,
            ]);

            // 4. Delete all user sessions
            DB::table('sessions')->where('user_id', $this->id)->delete();

            // 5. Delete all user tokens (Sanctum)
            $this->tokens()->delete();

            // 6. Execute hook after anonymization is complete
            $hookManager->execute('user.after_anonymize', null, [
                'user_id' => $this->id,
                'user_class' => get_class($this),
                'anonymous_email' => $anonymousId . '@deleted.local',
            ]);

            // Note: Orders, invoices, shipments, and payment transactions are preserved
            // They remain linked via polymorphic relationships but user data is anonymized
        });
    }

    /**
     * Check if user data has been anonymized.
     *
     * @return bool
     */
    public function isAnonymized(): bool
    {
        return !is_null($this->anonymized_at);
    }

    /**
     * Override the delete method to anonymize data before soft deletion.
     *
     * @return bool|null
     * @throws \Exception
     */
    public function delete()
    {
        // If force deleting, don't anonymize
        if ($this->isForceDeleting()) {
            return parent::delete();
        }

        // Anonymize data before soft delete
        if (!$this->isAnonymized()) {
            $this->anonymizeUserData();
        }

        // Perform soft delete
        return parent::delete();
    }
}
