<?php

namespace App\Console\Commands;

use App\Models\PozicijaTip;
use App\Observers\PozicijaTipObserver;
use Illuminate\Console\Command;

class SyncPozicijaTipRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pozicija:sync-roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync all PozicijaTip records with Spatie roles';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting to sync PozicijaTip records with roles...');
        
        $observer = new PozicijaTipObserver();
        $count = 0;
        
        PozicijaTip::all()->each(function ($pozicijaTip) use ($observer, &$count) {
            $observer->created($pozicijaTip);
            $count++;
            $this->line("Synced role for: {$pozicijaTip->naziv}");
        });
        
        $this->info("Completed! Synced {$count} PozicijaTip records with roles.");
        
        return Command::SUCCESS;
    }
}