<?php

namespace App\Services;

use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;

class GoogleMeetService
{
    protected $client;

    protected $calendarService;

    public function __construct()
    {
        $this->client = new Client;
        $this->client->setAuthConfig(storage_path('app/google/credentials.json'));
        $this->client->addScope(Calendar::CALENDAR);
        $this->calendarService = new Calendar($this->client);
    }

    public function createMeeting($summary, $startDateTime, $endDateTime, $attendees = [])
    {
        $event = new Event([
            'summary' => $summary,
            'start' => new EventDateTime([
                'dateTime' => $startDateTime,
                'timeZone' => config('app.timezone'),
            ]),
            'end' => new EventDateTime([
                'dateTime' => $endDateTime,
                'timeZone' => config('app.timezone'),
            ]),
            'conferenceData' => [
                'createRequest' => [
                    'requestId' => uniqid(),
                    'conferenceSolutionKey' => [
                        'type' => 'hangoutsMeet',
                    ],
                ],
            ],
            'attendees' => array_map(function ($email) {
                return ['email' => $email];
            }, $attendees),
        ]);

        $createdEvent = $this->calendarService->events->insert(
            'primary',
            $event,
            ['conferenceDataVersion' => 1]
        );

        return [
            'meet_link' => $createdEvent->getHangoutLink(),
            'event_id' => $createdEvent->getId(),
        ];
    }

    public function updateMeeting($eventId, $newStart, $newEnd)
    {
        $event = $this->calendarService->events->get('primary', $eventId);

        $event->setStart(new EventDateTime([
            'dateTime' => $newStart,
            'timeZone' => config('app.timezone'),
        ]));

        $event->setEnd(new EventDateTime([
            'dateTime' => $newEnd,
            'timeZone' => config('app.timezone'),
        ]));

        return $this->calendarService->events->update('primary', $eventId, $event);
    }

    public function cancelMeeting($eventId)
    {
        return $this->calendarService->events->delete('primary', $eventId);
    }
}
