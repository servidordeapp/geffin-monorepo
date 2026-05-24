<?php

use App\Enums\Auth\PasswordResetEventTypeEnum;
use App\Models\PasswordResetAuditEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('emailHash is deterministic and normalises email', function () {
    $hash1 = PasswordResetAuditEvent::emailHash('  User@Example.COM  ');
    $hash2 = PasswordResetAuditEvent::emailHash('user@example.com');

    expect($hash1)->toBe($hash2)
        ->and($hash1)->toBe(hash('sha256', 'user@example.com'));
});

it('has no updated_at column', function () {
    $event = PasswordResetAuditEvent::create([
        'event_type' => 'requested',
        'email_hash' => PasswordResetAuditEvent::emailHash('test@example.com'),
        'outcome' => 'accepted',
        'created_at' => now(),
    ]);

    expect($event->updated_at)->toBeNull()
        ->and(array_key_exists('updated_at', $event->toArray()))->toBeFalse();
});

it('accepts null user_id', function () {
    $event = PasswordResetAuditEvent::create([
        'event_type' => 'requested',
        'email_hash' => PasswordResetAuditEvent::emailHash('unknown@example.com'),
        'outcome' => 'accepted',
        'user_id' => null,
        'created_at' => now(),
    ]);

    expect($event->user_id)->toBeNull();
});

it('accepts each of the six event_type enum values', function (PasswordResetEventTypeEnum $eventType) {
    $event = PasswordResetAuditEvent::create([
        'event_type' => $eventType,
        'email_hash' => PasswordResetAuditEvent::emailHash('test@example.com'),
        'outcome' => 'accepted',
        'created_at' => now(),
    ]);

    expect($event->event_type)->toBe($eventType);
})->with(PasswordResetEventTypeEnum::cases());
