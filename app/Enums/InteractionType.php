<?php

namespace App\Enums;

enum InteractionType: string
{
    case PHONE_CALL = 'phone_call';
    case EMAIL = 'email';
    case MEETING = 'meeting';
    case VIDEO_CALL = 'video_call';
    case WHATSAPP = 'whatsapp';
    case SMS = 'sms';
    case IN_PERSON = 'in_person';
    case SLACK = 'slack';
    case TEAMS = 'teams';
    case OTHER = 'other';

    /**
     * Get the label for the interaction type
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::PHONE_CALL => 'Phone Call',
            self::EMAIL => 'Email',
            self::MEETING => 'Meeting',
            self::VIDEO_CALL => 'Video Call',
            self::WHATSAPP => 'WhatsApp',
            self::SMS => 'SMS',
            self::IN_PERSON => 'In Person',
            self::SLACK => 'Slack',
            self::TEAMS => 'Microsoft Teams',
            self::OTHER => 'Other',
        };
    }

    /**
     * Get the icon for the interaction type (for UI purposes)
     */
    public function getIcon(): string
    {
        return match ($this) {
            self::PHONE_CALL => 'phone',
            self::EMAIL => 'mail',
            self::MEETING => 'calendar',
            self::VIDEO_CALL => 'video',
            self::WHATSAPP => 'message-circle',
            self::SMS => 'message-square',
            self::IN_PERSON => 'users',
            self::SLACK => 'slack',
            self::TEAMS => 'video',
            self::OTHER => 'more-horizontal',
        };
    }

    /**
     * Get the color for the interaction type (for UI purposes)
     */
    public function getColor(): string
    {
        return match ($this) {
            self::PHONE_CALL => 'blue',
            self::EMAIL => 'gray',
            self::MEETING => 'green',
            self::VIDEO_CALL => 'purple',
            self::WHATSAPP => 'green',
            self::SMS => 'blue',
            self::IN_PERSON => 'orange',
            self::SLACK => 'purple',
            self::TEAMS => 'blue',
            self::OTHER => 'gray',
        };
    }

    /**
     * Check if this interaction type is digital
     */
    public function isDigital(): bool
    {
        return in_array($this, [
            self::EMAIL,
            self::VIDEO_CALL,
            self::WHATSAPP,
            self::SMS,
            self::SLACK,
            self::TEAMS,
        ]);
    }

    /**
     * Check if this interaction type is verbal
     */
    public function isVerbal(): bool
    {
        return in_array($this, [
            self::PHONE_CALL,
            self::MEETING,
            self::VIDEO_CALL,
            self::IN_PERSON,
        ]);
    }
}
