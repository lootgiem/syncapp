<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PlatformsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'me:platforms';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the command to install the application';

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
     *     */
    public function handle()
    {
        $platforms = config('platforms.list');

        foreach ($platforms as $key => $value) {

            $function = new \ReflectionClass($value['class']);

            $this->call('me:platform', [
                '--name' => $function->getShortName(),
                '--has_agendas' => $value['has_agendas'],
                '--readable_name' => $value['readable_name'],
                '--available' => $value['available']]);
        }

        $this->info(config('app.name') . ' successfully installed.');
    }
}
