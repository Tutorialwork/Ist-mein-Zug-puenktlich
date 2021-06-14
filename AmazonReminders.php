<?php


class AmazonReminders {

    private $remindersApiUrl = "https://api.eu.amazonalexa.com/v1/alerts/reminders";

    private function createReminder($authenticationToken, $text, $scheduledTime, $recurrence = false, $recurrenceRules = null): int {
        require_once "vendor/autoload.php";

        $payload = [
            'requestTime' => date('c'),
            'trigger' => [
                'type' => 'SCHEDULED_ABSOLUTE',
                'scheduledTime' => $scheduledTime,
                'timeZoneId' => 'Europe/Berlin',
            ],
            'alertInfo' => [
                'spokenInfo' => [
                    'content' => [
                        [
                            'locale' => 'de-DE',
                            'text' => $text,
                            'ssml' => '<speak>' . $text . '</speak>'
                        ]
                    ]
                ]
            ],
            'pushNotification' => [
                'status' => 'ENABLED'
            ]
        ];

        if ($recurrence) {
            $payload['trigger']['recurrence'] = [
                'recurrenceRules' => $recurrenceRules
            ];
        }

        error_log(json_encode($payload));

        try{

            $client = new GuzzleHttp\Client();
            $response = $client->request('POST', $this->remindersApiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $authenticationToken,
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode($payload)
            ]);

            return $response->getStatusCode();
        } catch(\GuzzleHttp\Exception\ClientException $exception){
            error_log("Error while requesting Amazon Reminders API: " . $exception->getMessage());

            return $exception->getCode();
        } catch (\GuzzleHttp\Exception\GuzzleException $guzzleException) {
            error_log("Error while requesting Amazon Reminders API: " . $guzzleException->getMessage());

            return $guzzleException->getCode();
        }
    }

    public function hasActiveReminders($authenticationToken): bool {
        try{

            $client = new GuzzleHttp\Client();
            $response = $client->request('GET', $this->remindersApiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $authenticationToken,
                    'Content-Type' => 'application/json',
                ],
            ]);

            $response = json_decode($response->getBody());

            $hasReminders = false;

            foreach ($response->alerts as $alert) {
                if ($alert->status == "ON") {
                    $hasReminders = true;
                }
            }

            return $hasReminders;
        } catch(\GuzzleHttp\Exception\ClientException $exception){
            error_log("Error while requesting Amazon Reminders API: " . $exception->getMessage());

            return false;
        } catch (\GuzzleHttp\Exception\GuzzleException $guzzleException) {
            error_log("Error while requesting Amazon Reminders API: " . $guzzleException->getMessage());

            return false;
        }
    }

    public function processRemindersRequest($departureDate, $recurrence, $accessToken): array {
        $builder = new ResponseBuilder();

        $notifyTime = $departureDate - 900;
        $hour = date('H', $notifyTime);
        $minute = date('i', $notifyTime);

        $targetDate = date('c', $notifyTime + 86400);
        $targetDate = explode('+', $targetDate)[0];
        $statusCode = $this->createReminder(
            $accessToken,
            'Denk daran die Verspätungen im "Ist mein Zug pünktlich" Skill abzufragen!',
            $targetDate,
            $recurrence,
            [
                "FREQ=WEEKLY;BYDAY=MO;BYHOUR=" . $hour . ";BYMINUTE=" . $minute . ";BYSECOND=0;INTERVAL=1;",
                "FREQ=WEEKLY;BYDAY=TU;BYHOUR=" . $hour . ";BYMINUTE=" . $minute . ";BYSECOND=0;INTERVAL=1;",
                "FREQ=WEEKLY;BYDAY=WE;BYHOUR=" . $hour . ";BYMINUTE=" . $minute . ";BYSECOND=0;INTERVAL=1;",
                "FREQ=WEEKLY;BYDAY=TH;BYHOUR=" . $hour . ";BYMINUTE=" . $minute . ";BYSECOND=0;INTERVAL=1;",
                "FREQ=WEEKLY;BYDAY=FR;BYHOUR=" . $hour . ";BYMINUTE=" . $minute . ";BYSECOND=0;INTERVAL=1;"
            ]
        );

        if ($statusCode == 401) {
            $builder->speechPermissionsCard(
                'Bitte gib dem Skill die nötigen Berechtigungen in der Alexa App.',
                ["alexa::alerts:reminders:skill:readwrite"]
            );
        } else if ($statusCode == 403) {
            $builder->speechText("Dein Gerät unterstützt dies nicht oder die maximale Anzahl von Erinnerungen ist erreicht.");
        } else if ($statusCode == 500) {
            $builder->speechText("Entschuldige, aber etwas ist schiefgelaufen. Bitte versuche es erneut.");
        } else {
            $builder->speechText("Du wirst nun von mir daran erinnert die Verspätungen abzufragen.");
        }

        return $builder->getResponse();
    }

}