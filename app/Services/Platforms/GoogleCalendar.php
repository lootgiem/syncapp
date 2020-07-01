<?php


namespace App\Services\Platforms;


use App\Models\Event;
use App\Repositories\EventRepository;
use App\Services\Connectors\OauthCodeConnector;
use App\Services\CredentialsStrategies\OauthStrategy;
use App\Services\GoogleOauthClient;
use Carbon\Carbon;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use LogicException;

class GoogleCalendar extends ConnectablePlatform
{
    /**
     * @var Google_Service_Calendar
     */
    protected Google_Service_Calendar $googleCalendarClient;

    public function __construct(Client $httpClient)
    {
        $credentialPath = config('platforms.list.google_calendar.credential_path');
        $scope = config('platforms.list.google_calendar.scope');
        $redirect_url = route('oauth.callback');
        $client = new GoogleOauthClient($credentialPath, $scope, $redirect_url, $httpClient);
        $connector = new OauthCodeConnector($client);
        $credentialStrategy = new OauthStrategy($connector);

        parent::__construct($connector, $credentialStrategy);
    }

    public function whenConnected()
    {
        $oauthClient = $this->getConnector()->getOauthClient();
        $this->googleCalendarClient = new Google_Service_Calendar($oauthClient);
    }

    /**
     * @param array $options
     * @return Collection
     */
    public function retrieveRawEventsFromPlatform($options = array())
    {
        $startDate = Carbon::now('Europe/Paris');
        $endDate = Carbon::now('Europe/Paris')->addMonths(config('synchronization.sync_period.months'));

        $optParams = array(
            'timeMax' => $endDate->toAtomString(),
            'timeMin' => $startDate->toAtomString(),
            'maxResults' => 2500
        );

        $agenda = $this->getCurrentCredential()->agenda;
        return $this->recursiveRetrieveEventsFromPlatform($agenda, $optParams);
    }

    /**
     * @param $agenda
     * @param array $optParams
     * @return Collection
     */
    private function recursiveRetrieveEventsFromPlatform($agenda, $optParams)
    {
        $eventsAccumulator = collect();
        $events = $this->googleCalendarClient->events->listEvents($agenda, $optParams);
        $eventsAccumulator = $eventsAccumulator->merge($events->getItems());

        if ($pageToken = $events->getNextPageToken()) {
            $optParams['pageToken'] = $pageToken;
            $eventsAccumulator = $eventsAccumulator->merge($this->recursiveRetrieveEventsFromPlatform($agenda, $optParams));
        }

        return $eventsAccumulator;
    }

    /**
     * @return Collection
     */
    protected function retrieveAgendas()
    {
        $agendas = $this->recursiveRetrieveAgendasFromPlatform();
        return $agendas->pluck('summary', 'id');
    }

    /**
     * @param array $optParams
     * @return Collection
     */
    private function recursiveRetrieveAgendasFromPlatform($optParams = [])
    {
        $agendasAccumulator = collect();
        $agendas = $this->googleCalendarClient->calendarList->listCalendarList($optParams);
        $agendasAccumulator = $agendasAccumulator->merge($agendas->getItems());

        if ($pageToken = $agendas->getNextPageToken()) {
            $optParams['pageToken'] = $pageToken;
            $agendasAccumulator = $agendasAccumulator->merge($this->recursiveRetrieveAgendasFromPlatform($optParams));
        }

        return $agendasAccumulator;
    }

    protected function filterRawEvents($rawEvents)
    {
        return $rawEvents->whereNull('recurrence');
    }

    /**
     * @param $rawEvent
     * @return string
     */
    public function pushEventToPlatform($rawEvent)
    {
        $agenda = $this->getCurrentCredential()->agenda;
        $pushedEvent = $this->googleCalendarClient->events->insert($agenda, $rawEvent);
        return $pushedEvent->id;
    }

    /**
     * @param $event
     */
    public function removeEventFromPlatform($event)
    {
        $agenda = $this->getCurrentCredential()->agenda;
        $this->googleCalendarClient->events->delete($agenda, $event->real_id);
    }

    /**
     * @param $event
     * @return Google_Service_Calendar_Event
     */
    public function transformEventForPlatform($event)
    {
        $dates = $this->getPlatformDate($event);

        return new Google_Service_Calendar_Event([
            'visibility' => "confidential",
            'summary' => $event->summary,
            'location' => $event->location,
            'description' => $event->description,
            'status' => 'confirmed',
            'locked' => (bool)$event->locked,
            'start' => [
                'date' => $dates['start_date'],
                'dateTime' => $dates['start_dateTime'],
                'timeZone' => "Europe/Paris",
            ],
            'end' => [
                'date' => $dates['end_date'],
                'dateTime' => $dates['end_dateTime'],
                'timeZone' => "Europe/Paris",
            ],
            'reminders' => [
                'useDefault' => FALSE,
                'overrides' => [
                    array('method' => 'email', 'minutes' => 24 * 60),
                    array('method' => 'popup', 'minutes' => 10),
                ],
            ]
        ]);
    }

    /**
     * @param $rawEvent
     * @return Event
     */
    public function transformToEvent($rawEvent)
    {
        $date = $this->getEventDate($rawEvent);

        return EventRepository::create([
            'credential_id' => $this->getCurrentCredential()->id,
            'real_id' => $rawEvent->getId(),
            'visibility' => $rawEvent->getVisibility(),
            'summary' => $rawEvent->getSummary(),
            'location' => $rawEvent->getLocation(),
            'description' => $rawEvent->getDescription(),
            'status' => $rawEvent->getStatus(),
            'locked' => $rawEvent->getLocked(),
            'all_day' => $date['all_day'],
            'start_date' => $date['start_date'],
            'end_date' => $date['end_date']
        ]);
    }

    /**
     * @param Event $event
     * @return array
     */
    private function getPlatformDate(Event $event)
    {
        $startDate = Carbon::createFromTimeString($event->start_date, 'Europe/Paris');
        $endDate = Carbon::createFromTimeString($event->end_date, 'Europe/Paris');

        if ($event->all_day) {
            return [
                "start_date" => $startDate->toDateString(),
                "start_dateTime" => null,
                "end_date" => $startDate->addDay()->toDateString(),
                "end_dateTime" => null,
            ];
        }

        return [
            "start_date" => null,
            "start_dateTime" => $startDate->toAtomString(),
            "end_date" => null,
            "end_dateTime" => $endDate->toAtomString(),
        ];
    }

    /**
     * @param Google_Service_Calendar_Event $event
     * @return array
     */
    private function getEventDate($event)
    {
        if (!is_null($event->getStart()->getDateTime())) {

            $startDate = Carbon::createFromFormat(Carbon::RFC3339, $event->getStart()->getDateTime());
            $endDate = Carbon::createFromFormat(Carbon::RFC3339, $event->getEnd()->getDateTime());

            return [
                'all_day' => 0,
                'start_date' => $startDate->toDateTimeString(),
                'end_date' => $endDate->toDateTimeString()
            ];
        }

        if (!is_null($event->getStart()->getDate())) {

            $startDate = Carbon::createFromFormat('!Y-m-d', $event->getStart()->getDate());

            return [
                'all_day' => 1,
                'start_date' => $startDate->toDateTimeString(),
                'end_date' => $startDate->addDay()->addMinutes(-1)->toDateTimeString()
            ];
        }

        throw new LogicException('Date not available for Google calendar event : ' . json_encode($event));
    }
}
