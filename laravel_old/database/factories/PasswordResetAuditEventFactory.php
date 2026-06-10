<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PasswordResetAuditEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PasswordResetAuditEvent>
 */
class PasswordResetAuditEventFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $email = $this->faker->safeEmail();

        return [
            'event_type' => 'requested',
            'user_id' => null,
            'email_hash' => PasswordResetAuditEvent::emailHash($email),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'outcome' => 'accepted',
            'reason' => null,
        ];
    }

    public function requested(): static
    {
        return $this->state(['event_type' => 'requested', 'outcome' => 'accepted']);
    }

    public function emailSent(): static
    {
        return $this->state(['event_type' => 'email_sent', 'outcome' => 'delivered']);
    }

    public function linkOpened(): static
    {
        return $this->state(['event_type' => 'link_opened', 'outcome' => 'accepted']);
    }

    public function passwordChanged(): static
    {
        return $this->state(['event_type' => 'password_changed', 'outcome' => 'accepted']);
    }

    public function tokenRejected(string $reason = 'invalid'): static
    {
        return $this->state(['event_type' => 'token_rejected', 'outcome' => 'rejected', 'reason' => $reason]);
    }

    public function requestThrottled(): static
    {
        return $this->state(['event_type' => 'request_throttled', 'outcome' => 'throttled']);
    }
}
