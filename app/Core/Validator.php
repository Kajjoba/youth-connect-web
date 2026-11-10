<?php
namespace App\Core;

/**
 * Server-side validation. Rules are pipe-separated, e.g. 'required|email|max:190'.
 * Messages are written to help the user fix the problem, never to blame them.
 */
class Validator
{
    private array $errors = [];
    private array $clean = [];

    public static function make(array $input, array $rules, array $labels = []): self
    {
        $v = new self();
        foreach ($rules as $field => $ruleStr) {
            $raw = $input[$field] ?? null;
            $val = is_string($raw) ? trim($raw) : $raw;
            $label = $labels[$field] ?? ucfirst(str_replace('_', ' ', $field));
            $rulesArr = explode('|', $ruleStr);
            $nullable = !in_array('required', $rulesArr, true);
            $empty = ($val === null || $val === '' || $val === []);
            if ($empty && $nullable) { $v->clean[$field] = null; continue; }
            foreach ($rulesArr as $r) {
                [$name, $arg] = array_pad(explode(':', $r, 2), 2, null);
                $msg = $v->check($name, $arg, $val, $input, $label);
                if ($msg !== null) { $v->errors[$field] = $msg; break; }
            }
            $v->clean[$field] = $val;
        }
        return $v;
    }

    private function check(string $rule, ?string $arg, $val, array $input, string $label): ?string
    {
        switch ($rule) {
            case 'required':
                return ($val === null || $val === '' || $val === []) ? "Please enter $label." : null;
            case 'email':
                return filter_var($val, FILTER_VALIDATE_EMAIL) ? null : 'Enter an email address like name@example.com.';
            case 'min':
                return mb_strlen((string)$val) >= (int)$arg ? null : "$label needs at least $arg characters.";
            case 'max':
                return mb_strlen((string)$val) <= (int)$arg ? null : "$label can be at most $arg characters.";
            case 'int':
                return filter_var($val, FILTER_VALIDATE_INT) !== false ? null : "$label must be a whole number.";
            case 'between':
                [$a, $b] = explode(',', (string)$arg);
                return (filter_var($val, FILTER_VALIDATE_INT) !== false && $val >= $a && $val <= $b)
                    ? null : "$label must be between $a and $b.";
            case 'in':
                return in_array((string)$val, explode(',', (string)$arg), true) ? null : "Choose one of the listed options for $label.";
            case 'url':
                return (filter_var($val, FILTER_VALIDATE_URL) && preg_match('#^https?://#i', (string)$val))
                    ? null : 'Enter a full web address starting with http:// or https://.';
            case 'date':
                $d = \DateTime::createFromFormat('Y-m-d', (string)$val);
                return ($d && $d->format('Y-m-d') === $val) ? null : "Enter $label as a date (YYYY-MM-DD).";
            case 'confirmed':
                return ($val === ($input[$arg ?: '' ] ?? null)) ? null : "The two entries for $label do not match.";
            case 'strongpass':
                return (preg_match('/[A-Za-z]/', (string)$val) && preg_match('/\d/', (string)$val))
                    ? null : 'Use at least one letter and one number in your password.';
        }
        return null;
    }

    public function fails(): bool { return !empty($this->errors); }
    public function errors(): array { return $this->errors; }
    public function data(): array { return $this->clean; }
}
