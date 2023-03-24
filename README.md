# Phonovation notification channel for Laravel

This package makes it easy to send notifications using Phonovation with Laravel framework.
Since Laravel already ships with SES email support, this package focuses on sending only SMS notifications for now.
More advanced features like support for topics could be added in the future.

Based on:

https://github.com/laravel-notification-channels/aws-sns/

## Contents

## Installation

You can install the package via composer:

``` bash
composer require dermview/laravel-phonovation-channel --update-with-dependencies
```

### Setting up

Populate .env

```dotenv
PHONOVATION_API_URL=https://api.interactsms.com/HTTP_API/V1/sendmessage.aspx
PHONOVATION_API_USER=Test
PHONOVATION_API_PASSWORD=Pass
PHONOVATION_API_ID=<yourid>
PHONOVATION_API_FROM=YourName
```

Create following file **config/phonovation.php**

```php
<?php
return [
    'url' => env('PHONOVATION_API_URL', 'https://api.interactsms.com/HTTP_API/V1/sendmessage.aspx'),
    'query_params' => [
        'user' => env('PHONOVATION_API_USER', 'Test'),
        'password' => env('PHONOVATION_API_PASSWORD', ''),
        'api_id' => env('PHONOVATION_API_ID', ''),
        'from' => env('PHONOVATION_API_FROM', 'FreeText'),
    ],
    'whitelist' => [
        '+yourmobile4test'
    ]
];
```

## Usage

Now you can use the channel in your `via()` method inside the notification:

```php
<?php

use NotificationChannels\Phonovation\PhonovationChannel;
use NotificationChannels\Phonovation\PhonovationMessage;
use Illuminate\Notifications\Notification;

class AccountApproved extends Notification
{
    public function via($notifiable)
    {
        return [PhonovationChannel::class];
    }

    public function toPhonovation($notifiable)
    {
        // You can just return a plain string:
        return "Your {$notifiable->service} account was approved!";
        
        // OR explicitly return a PhonovationMessage object passing the message body:
        return new PhonovationMessage("Your {$notifiable->service} account was approved!");
        
        // OR return a PhonovationMessage passing the arguments via `create()` or `__construct()`:
        return PhonovationMessage::create([
            'body' => "Your {$notifiable->service} account was approved!",
            'transactional' => true,
            'sender' => 'MyBusiness',
        ]);

        // OR create the object with or without arguments and then use the fluent API:
        return PhonovationMessage::create()
            ->body("Your {$notifiable->service} account was approved!")
            ->promotional()
            ->sender('MyBusiness');
    }
}
```

In order to let your Notification know which phone are you sending to, the channel
will look for the `phone`, `phone_number` or `full_phone` attribute of the
Notifiable model. If you want to override this behaviour, add the
`routeNotificationForSns` method to your Notifiable model.

```php
<?php

use Illuminate\Notifications\Notifiable;

class SomeModel {
    use Notifiable;

    public function routeNotificationForSns($notification)
    {
        return '+353870000000';
    }
}
```

### Available SnsMessage methods

- `create([])`: Accepts an array of key-values where the keys correspond to the methods below and the values are passed
  as parameters;
- `body('')`: Accepts a string value for the notification body. Messages with more than 140 characters will be split
  into multiple messages by SNS without breaking any words;
- `promotional(bool)`: Sets the delivery type as promotional (default). Optimizes the delivery for lower costs;
- `transactional(bool)`: Sets the delivery type as transactional. Optimizes the delivery to achieve the highest
  reliability (it also costs more);
- `sender(string)`: Up to 11 characters with no spaces, that is displayed as the sender on the receiving
  device. [Support varies by country]

## Common Problems

### Exception Handling

Exceptions are not thrown by the package in order to give other channels a chance to work properly. Instead,
a `Illuminate\Notifications\Events\NotificationFailed` event is dispatched. For debugging purposes you may listen to
this event in the `boot` method of `EventServiceProvider.php`.

```php
Event::listen(function (\Illuminate\Notifications\Events\NotificationFailed $event) {
    //Dump and die
    dd($event);
    
    //or log the event
    Log::error('Phonovation error', $event->data)
});
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Security

If you discover any security related issues, please email it@dermview.ie instead of using the issue tracker.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

https://github.com/laravel-notification-channels/aws-sns/

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
