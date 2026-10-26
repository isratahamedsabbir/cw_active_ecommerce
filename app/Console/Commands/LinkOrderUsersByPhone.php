<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LinkOrderUsersByPhone extends Command
{
    /**
     * orders.customer_address is stored inconsistently in this table: some rows
     * hold a plain JSON object, others hold a JSON-encoded string of that same
     * JSON object (double-encoded), so it needs up to two json_decode() passes.
     *
     * @var string
     */
    protected $signature = 'orders:link-users-by-phone
        {--apply : Actually write changes. Without this flag the command only reports what it would do.}
        {--limit= : Only process this many orders (for testing on a subset).}';

    protected $description = 'Find-or-create users from orders.customer_address (matched by phone) and repoint orders.user_id to them';

    private array $phoneToUserId = [];
    private array $emailsInUse = [];

    public function handle()
    {
        $apply = (bool) $this->option('apply');
        $limit = $this->option('limit');

        $this->warn($apply ? 'RUNNING IN APPLY MODE — changes will be written.' : 'DRY RUN — no changes will be written. Pass --apply to commit.');

        $this->loadExistingUsers();

        $stats = [
            'total' => 0,
            'unchanged' => 0,
            'relinked_to_existing' => 0,
            'new_user_created' => 0,
            'unresolvable_phone' => 0,
            'email_conflict_skipped' => 0,
        ];

        $unresolvableSamples = [];
        $changes = [];

        $query = Order::query()->select('id', 'user_id', 'customer_address')->orderBy('id');
        if ($limit) {
            $query->limit((int) $limit);
        }

        $work = function () use ($query, $apply, &$stats, &$unresolvableSamples, &$changes) {
            $query->chunkById(200, function ($orders) use ($apply, &$stats, &$unresolvableSamples, &$changes) {
                foreach ($orders as $order) {
                    $stats['total']++;

                    $address = $this->decodeCustomerAddress($order->getRawOriginal('customer_address'));
                    $phone = $this->normalizePhone($address['phone'] ?? null);

                    if ($phone === null) {
                        $stats['unresolvable_phone']++;
                        if (count($unresolvableSamples) < 15) {
                            $unresolvableSamples[] = "order #{$order->id}: raw phone = " . json_encode($address['phone'] ?? null);
                        }
                        continue;
                    }

                    $email = $this->normalizeEmail($address['email'] ?? null);
                    $name = trim((string) ($address['name'] ?? '')) ?: 'Customer';

                    if (isset($this->phoneToUserId[$phone])) {
                        $userId = $this->phoneToUserId[$phone];

                        // Backfill email on the matched user if they don't have one yet.
                        if ($email && $this->canUseEmail($email, $userId)) {
                            $user = User::find($userId);
                            if ($user && empty($user->email)) {
                                if ($apply) {
                                    $user->email = $email;
                                    $user->save();
                                }
                                $this->emailsInUse[$email] = $userId;
                            }
                        }
                    } else {
                        $stats['new_user_created']++;
                        $useEmail = $email && $this->canUseEmail($email, null) ? $email : null;

                        if ($email && !$useEmail) {
                            $stats['email_conflict_skipped']++;
                        }

                        if ($apply) {
                            $user = User::create([
                                'name' => $name,
                                'phone' => $phone,
                                'email' => $useEmail,
                                'password' => Hash::make(Str::random(16)),
                            ]);
                            $userId = $user->id;
                        } else {
                            // Fake negative id so a dry run can still report consistent numbers.
                            $userId = -1 * (count($this->phoneToUserId) + 1);
                        }

                        $this->phoneToUserId[$phone] = $userId;
                        if ($useEmail) {
                            $this->emailsInUse[$useEmail] = $userId;
                        }
                    }

                    if ($userId !== $order->user_id) {
                        $changes[] = [$order->id, $order->user_id, $userId, $phone];
                        if ($apply) {
                            Order::where('id', $order->id)->update(['user_id' => $userId]);
                        }
                    } else {
                        $stats['unchanged']++;
                    }
                }
            });
        };

        if ($apply) {
            DB::transaction($work);
        } else {
            $work();
        }

        // Recompute relinked_to_existing cleanly: total changes minus new users created.
        $stats['relinked_to_existing'] = count($changes) - $stats['new_user_created'];

        $this->newLine();
        $this->info('--- Summary ---');
        foreach ($stats as $key => $value) {
            $this->line(str_pad($key, 24) . ': ' . $value);
        }

        if (!empty($unresolvableSamples)) {
            $this->newLine();
            $this->warn('Sample orders with no usable phone (left untouched):');
            foreach ($unresolvableSamples as $line) {
                $this->line('  ' . $line);
            }
        }

        if (!empty($changes)) {
            $this->newLine();
            $this->info('Sample user_id changes (order_id, old_user_id, new_user_id, phone):');
            foreach (array_slice($changes, 0, 20) as $c) {
                $this->line('  #' . $c[0] . ': ' . $c[1] . ' -> ' . $c[2] . ' (' . $c[3] . ')');
            }
            if (count($changes) > 20) {
                $this->line('  ... and ' . (count($changes) - 20) . ' more');
            }
        }

        return self::SUCCESS;
    }

    private function loadExistingUsers(): void
    {
        User::query()->select('id', 'phone', 'email')->orderBy('id')->chunk(500, function ($users) {
            foreach ($users as $user) {
                $phone = $this->normalizePhone($user->phone);
                if ($phone && !isset($this->phoneToUserId[$phone])) {
                    $this->phoneToUserId[$phone] = $user->id;
                }
                if ($user->email) {
                    $this->emailsInUse[strtolower($user->email)] = $user->id;
                }
            }
        });
    }

    private function canUseEmail(string $email, ?int $forUserId): bool
    {
        $owner = $this->emailsInUse[$email] ?? null;
        return $owner === null || $owner === $forUserId;
    }

    private function normalizeEmail(?string $email): ?string
    {
        $email = trim((string) $email);
        if ($email === '' || strtolower($email) === 'null' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }
        return strtolower($email);
    }

    /**
     * Normalizes to an 11-digit "01XXXXXXXXX" Bangladeshi mobile format.
     * Returns null when no valid BD mobile number can be extracted.
     */
    private function normalizePhone(?string $raw): ?string
    {
        if ($raw === null) {
            return null;
        }

        $raw = trim($raw);
        if ($raw === '' || strtolower($raw) === 'null') {
            return null;
        }

        // Bengali-Arabic digits -> ASCII digits.
        $bengali = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
        $ascii = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $raw = str_replace($bengali, $ascii, $raw);

        $digits = preg_replace('/\D+/', '', $raw);

        // Strip a leading country code.
        if (str_starts_with($digits, '880') && strlen($digits) >= 13) {
            $digits = substr($digits, 3);
        }

        // 10 digits starting with 1 (no leading 0) -> add it.
        if (strlen($digits) === 10 && $digits[0] === '1') {
            $digits = '0' . $digits;
        }

        if (preg_match('/^01[3-9]\d{8}$/', $digits)) {
            return $digits;
        }

        // Fallback: pull an embedded valid BD mobile number out of noisy input,
        // e.g. "999(01799624255)".
        if (preg_match('/01[3-9]\d{8}/', $digits, $m)) {
            return $m[0];
        }

        return null;
    }

    /**
     * customer_address is sometimes plain JSON, sometimes JSON-encoded twice.
     */
    private function decodeCustomerAddress(?string $raw): array
    {
        if (!$raw) {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true);
        }

        return is_array($decoded) ? $decoded : [];
    }
}
