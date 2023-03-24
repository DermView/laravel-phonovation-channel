<?php

namespace NotificationChannels\Phonovation;

use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;

class PhonovationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->app->when(PhonovationChannel::class)
            ->give(function () {
                return new Phonovation();
            });

        $this->app->bind(PhonovationService::class, function () {
            $config = array_merge(['version' => 'latest'], $this->app['config']['services.sns']);

            return new PhonovationService($this->addSnsCredentials($config));
        });
    }

}
