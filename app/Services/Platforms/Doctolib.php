<?php


namespace App\Services\Platforms;


use App\Models\Event;
use App\Repositories\EventRepository;
use App\Services\Connectors\UsernamePasswordConnector;
use App\Services\CredentialsStrategies\CredentialStrategy;
use App\Services\DoctolibApi;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;

class Doctolib extends ConnectablePlatform
{
    /**
     * @var DoctolibApi
     */
    protected DoctolibApi $doctolibApi;

    public function __construct(Client $httpClient)
    {
        $loginUrl = config('platforms.list.doctolib.login_endpoint');
        $data = ['kind' => 'doctor'];

        $connector = new UsernamePasswordConnector($httpClient, $loginUrl, $data);
        $credentialStrategy = new CredentialStrategy($connector);
        parent::__construct($connector, $credentialStrategy);
    }

    public function whenConnected()
    {
        $httpClient = $this->getConnector()->getHttpClient();
        $this->doctolibApi = new DoctolibApi($httpClient);
    }

    /**
     * @param $rawEvent
     */
    public function removeEventFromPlatform($rawEvent)
    {
        $this->doctolibApi->removeAbsence($rawEvent->real_id);
    }

    /**
     * Override parent method.
     *
     * @param $rawEvents
     * @return Collection
     */
    public function filterRawEvents($rawEvents)
    {
        return $rawEvents->where('type', 'blck')
            ->where('recurring', false)
            ->merge($rawEvents->where('type', 'appt'));
    }

    /**
     * @param $rawEvent
     * @return array|string
     */
    public function pushEventToPlatform($rawEvent)
    {
        $pushedEvents = $this->doctolibApi->addAbsence($rawEvent);
        return $pushedEvents['id'];
    }

    /**
     * @return Collection $event
     */
    public function retrieveRawEventsFromPlatform()
    {
        $startDate = Carbon::now('Europe/Paris');
        $endDate = Carbon::now('Europe/Paris')->addMonths(config('synchronization.sync_period.months'));
        $agenda = $this->getCurrentCredential()->agenda;

        $parameters = [
            "start_date" => $startDate->format("D M j Y H:i:s eO"),
            "end_date" => $endDate->format("D M j Y H:i:s eO"),
            'agenda_ids[]' => $agenda
        ];

        return $this->doctolibApi->getEvents($parameters);
    }

    /**
     * @return Collection
     */
    protected function retrieveAgendas()
    {
        $agendas = collect($this->doctolibApi->getAccountInformation()->agendas);
        return $agendas->pluck('name', 'id');
    }

    /**
     * @param $event
     * @return array
     */
    public function transformEventForPlatform($event)
    {
        return [
            'agenda_id' => $this->getCurrentCredential()->agenda,
            'all_day' => boolval($event->all_day),
            'start_date' => $event->start_date,
            'end_date' => $event->end_date,
            'title' => $event->summary,
            'type' => "blck"
        ];
    }

    /**
     * @param $rawEvent
     * @return Event
     */
    public function transformToEvent($rawEvent)
    {
        $startDate = Carbon::createFromFormat(Carbon::RFC3339_EXTENDED, $rawEvent->start_date);
        $endDate = Carbon::createFromFormat(Carbon::RFC3339_EXTENDED, $rawEvent->end_date);

        return EventRepository::create([
            'credential_id' => $this->getCurrentCredential()->id,
            'real_id' => $rawEvent->id,
            'visibility' => "confidential",
            'summary' => $this->getSummary($rawEvent),
            'location' => $this->getLocation($rawEvent),
            'description' => $this->getDescription($rawEvent),
            'status' => 'confirmed',
            'locked' => true,
            'all_day' => false,
            'start_date' => $startDate->toDateTimeString(),
            'end_date' => $endDate->toDateTimeString()
        ]);
    }

    /**
     * @param $rawEvent
     * @return string
     */
    private function getLocation($rawEvent)
    {
        if ($rawEvent->type == 'appt') {
            return strtolower($this->retrieveAgendas()->get($rawEvent->agenda_id, ""));
        }

        return null;
    }

    /**
     * @param array $rawEvent
     * @return string
     */
    private function getSummary($rawEvent)
    {
        if ($rawEvent->type == 'appt') {
            $patient = $rawEvent->patient;
            $formatedName = $patient->last_name . ' ' . $patient->first_name;
            $status = ($rawEvent->status != "confirmed") ? ' Annulé - ' : '';
            $isNewMessage = (boolval($rawEvent->new_patient)) ? ' (Première fois)' : '';
            return $status . $formatedName . $isNewMessage;
        }

        return $rawEvent->title ?? 'Absence';
    }

    /**
     * @param array $rawEvent
     * @return string
     */
    private function getDescription($rawEvent)
    {
        if ($rawEvent->type == 'appt') {
            $patient = $rawEvent->patient;
            $formatedName = $patient->last_name . ' ' . $patient->first_name;
            return 'Rendez-vous avec ' . $formatedName
                . "\nTel : " . $patient->phone_number
                . "\nEmail : " . $patient->email
                . "\nAdresse : " . $this->getAddress($patient)
                . "\nInfo Crucial : " . $patient->crucial_info
                . "\nNote : " . $patient->notes;
        }

        return null;
    }

    /**
     * @param array $patient
     * @return string
     */
    private function getAddress($patient)
    {
        if (!empty($patient->address) && !empty($patient->city) && !empty($patient->zipcode)) {
            return $patient->address . ' / ' . $patient->city . ' / ' . $patient->zipcode;
        }
        else {
            return 'Non renseigné.';
        }
    }
}
