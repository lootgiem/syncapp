<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RefreshCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'me:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh all with the database seeding';

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
     */
    public function handle()
    {
        $this->call('migrate:refresh');
        $this->call('me:platforms');
        $this->call('db:seed');

        $this->info(config('app.name') . ' successfully refreshed.');
    }
}
