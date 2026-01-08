<?php

namespace Ingenius\Auth\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\File;

class SetupEmailVerificationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auth:setup-email-verification
                            {--tenant : Setup for tenant users (default)}
                            {--central : Setup for central users}
                            {--check : Only check if email verification is properly configured}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup or verify email verification configuration for user models';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isCentral = $this->option('central');
        $isTenant = $this->option('tenant') || !$isCentral;
        $checkOnly = $this->option('check');

        if ($isTenant) {
            $this->handleTenantSetup($checkOnly);
        }

        if ($isCentral) {
            $this->handleCentralSetup($checkOnly);
        }

        return 0;
    }

    /**
     * Handle tenant email verification setup.
     */
    protected function handleTenantSetup(bool $checkOnly): void
    {
        $this->info('🔍 Checking tenant user email verification setup...');
        $this->newLine();

        $userClass = tenant_user_class();

        if (!class_exists($userClass)) {
            $this->error("❌ Tenant user class not found: {$userClass}");
            $this->info("💡 Configure it in config/core.php: 'tenant_user_model'");
            return;
        }

        $this->info("✓ User class: {$userClass}");

        // Check if implements MustVerifyEmail
        $implementsMustVerify = in_array(MustVerifyEmail::class, class_implements($userClass));

        if ($implementsMustVerify) {
            $this->info("✓ Implements MustVerifyEmail interface");
        } else {
            $this->error("❌ Does NOT implement MustVerifyEmail interface");
        }

        // Check if uses the trait
        $usesTrait = $this->classUsesTrait($userClass, 'Ingenius\Auth\Traits\MustVerifyEmailForTenant');

        if ($usesTrait) {
            $this->info("✓ Uses MustVerifyEmailForTenant trait");
        } else {
            $this->warn("⚠ Does NOT use MustVerifyEmailForTenant trait");
        }

        // Check if email_verified_at column exists
        $hasColumn = $this->checkModelHasColumn($userClass, 'email_verified_at');
        if ($hasColumn) {
            $this->info("✓ Has 'email_verified_at' column");
        } else {
            $this->warn("⚠ Missing 'email_verified_at' column");
        }

        $this->newLine();

        if ($implementsMustVerify && $usesTrait && $hasColumn) {
            $this->info("✅ Email verification is properly configured for tenant users!");
            return;
        }

        if ($checkOnly) {
            $this->error("❌ Email verification is NOT properly configured");
            $this->showFixInstructions($userClass, $implementsMustVerify, $usesTrait, false);
            return;
        }

        // Offer to fix the issues
        if (!$implementsMustVerify || !$usesTrait) {
            $this->showFixInstructions($userClass, $implementsMustVerify, $usesTrait, false);
        }
    }

    /**
     * Handle central email verification setup.
     */
    protected function handleCentralSetup(bool $checkOnly): void
    {
        $this->info('🔍 Checking central user email verification setup...');
        $this->newLine();

        $userClass = central_user_class();

        if (!class_exists($userClass)) {
            $this->error("❌ Central user class not found: {$userClass}");
            $this->info("💡 Configure it in config/core.php: 'central_user_model'");
            return;
        }

        $this->info("✓ User class: {$userClass}");

        // Check if implements MustVerifyEmail
        $implementsMustVerify = in_array(MustVerifyEmail::class, class_implements($userClass));

        if ($implementsMustVerify) {
            $this->info("✓ Implements MustVerifyEmail interface");
        } else {
            $this->error("❌ Does NOT implement MustVerifyEmail interface");
        }

        // Check if uses the trait
        $usesTrait = $this->classUsesTrait($userClass, 'Ingenius\Core\Traits\MustVerifyEmailForCentral');

        if ($usesTrait) {
            $this->info("✓ Uses MustVerifyEmailForCentral trait");
        } else {
            $this->warn("⚠ Does NOT use MustVerifyEmailForCentral trait");
        }

        // Check if email_verified_at column exists
        $hasColumn = $this->checkModelHasColumn($userClass, 'email_verified_at');
        if ($hasColumn) {
            $this->info("✓ Has 'email_verified_at' column");
        } else {
            $this->warn("⚠ Missing 'email_verified_at' column");
        }

        $this->newLine();

        if ($implementsMustVerify && $usesTrait && $hasColumn) {
            $this->info("✅ Email verification is properly configured for central users!");
            return;
        }

        if ($checkOnly) {
            $this->error("❌ Email verification is NOT properly configured");
            $this->showFixInstructions($userClass, $implementsMustVerify, $usesTrait, true);
            return;
        }

        // Offer to fix the issues
        if (!$implementsMustVerify || !$usesTrait) {
            $this->showFixInstructions($userClass, $implementsMustVerify, $usesTrait, true);
        }
    }

    /**
     * Check if a class uses a specific trait.
     */
    protected function classUsesTrait(string $class, string $trait): bool
    {
        $traits = class_uses_recursive($class);
        return in_array($trait, $traits);
    }

    /**
     * Check if model has a specific column in its fillable or casts.
     */
    protected function checkModelHasColumn(string $class, string $column): bool
    {
        try {
            $model = new $class;
            $fillable = $model->getFillable();
            $casts = $model->getCasts();

            return in_array($column, $fillable) || array_key_exists($column, $casts);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Show instructions to fix the configuration.
     */
    protected function showFixInstructions(string $userClass, bool $hasInterface, bool $hasTrait, bool $isCentral): void
    {
        $this->newLine();
        $this->warn("📝 To enable email verification, update your User model:");
        $this->newLine();

        $traitName = $isCentral ? 'MustVerifyEmailForCentral' : 'MustVerifyEmailForTenant';
        $traitNamespace = $isCentral ? 'Ingenius\\Core\\Traits' : 'Ingenius\\Auth\\Traits';

        $code = "<?php\n\n";
        $code .= "namespace YourNamespace;\n\n";
        $code .= "use Illuminate\\Contracts\\Auth\\MustVerifyEmail;\n";
        $code .= "use {$traitNamespace}\\{$traitName};\n";
        $code .= "// ... other imports\n\n";
        $code .= "class User extends Authenticatable implements MustVerifyEmail\n";
        $code .= "{\n";
        $code .= "    use {$traitName};\n";
        $code .= "    // ... other traits\n";
        $code .= "}\n";

        $this->line($code);

        $this->info("Required changes:");
        if (!$hasInterface) {
            $this->line("  1. Add 'MustVerifyEmail' to the implements clause");
        }
        if (!$hasTrait) {
            $this->line("  2. Add '{$traitName}' to the use statements in the class body");
        }

        $this->newLine();
        $this->info("Then, make sure to:");
        $this->line("  • Add verification routes (see documentation)");
        $this->line("  • Update registration controller to send verification email");
        $this->line("  • Add 'verified' middleware to protected routes");
    }
}
