<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ClearExpiredTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:name';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    // public function handle()
    // {
    //     return Command::SUCCESS;
    // }

    // Inside the handle method in App\Console\Commands\ClearExpiredTokens.php

public function handle()
{
    $expiredTokens = DB::table('personal_access_tokens')
        ->where('expires_at', '<', now())
        ->delete();

    $this->info("Cleared $expiredTokens expired tokens from the database.");
}

}
