<?php

namespace App\Console\Commands;

use App\Repositories\PlatformRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class PlatformCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'me:platform
            {--name= : The name of the platform}
            {--readable_name= : The readable name of the platform}
            {--available= : The availability of the platform}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a platform for issuing synchronization services';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @param PlatformRepository $platformRepository
     * @return mixed
     */
    public function handle(PlatformRepository $platformRepository)
    {
        $name = $this->askString('name', 'What should be the Platform name?');
        $readableName = $this->askString('readable_name', 'What should be the readable name for ' . $name . '?', Str::kebab($name));
        $available = $this->askBoolean('available', 'Is ' . $name . ' platform available?', true);

        $platform = $platformRepository->findByName($name);

        $available_str = (!$available) ? ' but not available for clients' : ' and available for clients';

        if (!is_null($platform)) {
            $platform = $platformRepository->update($platform, $name, $readableName, $available);
            $this->line('<comment>Platform ' . $platform->name . ' (' . $platform->readable_name . ') updated' . $available_str . '.</comment> ');
        }
        else {
            $platform = $platformRepository->store($name, $readableName, $available);
            $this->line('<comment>Platform ' . $platform->name . ' (' . $platform->readable_name . ') created' . $available_str . '.</comment> ');
        }
    }

    private function askString($option_name, $question, $default = null)
    {
        return $this->option($option_name) ?: $this->ask($question, $default);
    }

    private function askBoolean($option_name, $question, $default = null)
    {
        $option = $this->option($option_name);

        return (!is_null($option)) ? $option : $this->confirm($question, $default);
    }

}
