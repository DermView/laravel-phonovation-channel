<?php

namespace NotificationChannels\Phonovation\Exceptions;

use NotificationChannels\Phonovation\PhonovationMessage;

class CouldNotSendNotification extends \Exception
{
    public static function invalidReceiver()
    {
        return new static(
            'The notifiable did not have a receiving phone number. Add a routeNotificationForSns
            method or one of the conventional attributes to your notifiable.'
        );
    }

    public static function invalidMessageObject($message)
    {
        $type = is_object($message) ? get_class($message) : gettype($message);

        return new static(
            'Notification was not sent. The message should be a instance of `'.PhonovationMessage::class."` and a `{$type}` was given."
        );
    }
}
