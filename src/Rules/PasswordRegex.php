<?php

namespace Ingenius\Auth\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PasswordRegex implements ValidationRule
{
    /**
     * The regex pattern for password validation.
     * Requirements:
     * - At least one lowercase letter
     * - At least one uppercase letter
     * - At least one digit
     * - At least one special character from @$!%*?&#._-
     * - Only allows letters, digits, and the special characters listed above
     */
    protected string $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#._\-])[A-Za-z\d@$!%*?&#._\-]+$/';

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!preg_match($this->pattern, $value)) {
            $fail(__('auth::validation.password.complexity'));
        }
    }

    /**
     * Get the regex pattern.
     */
    public function getPattern(): string
    {
        return $this->pattern;
    }
}
