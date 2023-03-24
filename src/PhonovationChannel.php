<?php

namespace NotificationChannels\Phonovation;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Notifications\Notification;
use NotificationChannels\Phonovation\Exceptions\CouldNotSendNotification;

class PhonovationChannel
{
    /**
     * @var Dispatcher
     */
    protected $events;

    public function __construct(Phonovation $sns, Dispatcher $events)
    {
        $this->events = $events;
    }

    /**
     * Send the given notification.
     *
     * @return \Aws\Result
     */
    public function send($notifiable, Notification $notification)
    {
        try {
            $destination = $this->getDestination($notifiable, $notification);
            $message = $this->getMessage($notifiable, $notification);

            return Phonovation::send($message, $destination);
        } catch (\Exception $e) {
            $event = new NotificationFailed(
                $notifiable,
                $notification,
                'sns',
                ['message' => $e->getMessage(), 'exception' => $e]
            );
            $this->events->dispatch($event);
        }
    }

    /**
     * Get the phone number to send a notification to.
     *
     * @throws CouldNotSendNotification
     */
    protected function getDestination($notifiable, Notification $notification)
    {
        if ($to = $notifiable->routeNotificationFor('phonovation', $notification)) {
            return $to;
        }
        return $this->guessDestination($notifiable);
    }

    /**
     * Try to get the phone number from some commonly used attributes for that.
     *
     * @throws CouldNotSendNotification
     */
    protected function guessDestination($notifiable)
    {
        $commonAttributes = ['phone', 'phone_number', 'full_phone'];
        foreach ($commonAttributes as $attribute) {
            if (isset($notifiable->{$attribute})) {
                return $notifiable->{$attribute};
            }
        }
        throw CouldNotSendNotification::invalidReceiver();
    }

    /**
     * Get the SNS Message object.
     *
     * @throws CouldNotSendNotification
     */
    protected function getMessage($notifiable, Notification $notification): PhonovationMessage
    {
        $message = $notification->toSns($notifiable);
        if (is_string($message)) {
            return new PhonovationMessage($message);
        }
        if ($message instanceof PhonovationMessage) {
            return $message;
        }
        throw CouldNotSendNotification::invalidMessageObject($message);
    }
}
