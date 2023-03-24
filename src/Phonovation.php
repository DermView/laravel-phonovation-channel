<?php

namespace NotificationChannels\Phonovation;

use Aws\Exception\AwsException;
use Aws\Sns\SnsClient as SnsService;

class Phonovation
{

    /**
     * @param string $destination Phone number as described by the E.164 format.
     */
    public function send(PhonovationMessage $message, $destination): void
    {
        self::actualSend($destination, $message->getBody());
    }

    private static function actualSend(string $phone, string $text): void
    {
        $apiConfig = config('phonovation');
        $apiUrl = $apiConfig['url'] ?? '';
        $queryParams = $apiConfig['query_params'] ?? [];

        if (!$apiUrl || !$queryParams) {
            Log::notice(__METHOD__ . ' mandatory settings misconfigured');
            return;
        }

        $queryParams = array_merge($queryParams, [
            'to' => $phone,
            'text' => $text,
        ]);

        if (env('APP_ENV','production') !== 'production') {
            $whiteList = $apiConfig['whitelist'] ?? [];

            if (!in_array($phone, $whiteList, true)) {
                Log::debug(sprintf('%s phone number is not whitelisted: %s ', __METHOD__, $phone));
                return;
            }
        }

        $response = Http::get($apiConfig['url'], $queryParams);

        if (!$response->successful()) {
            Log::warning(sprintf('%s response failed. status: %d. body: %s', __METHOD__, $response->status(), $response->object()));
        }
    }

}
