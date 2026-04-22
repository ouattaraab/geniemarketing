<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('gm:subscriptions:remind-renewal')]
#[Description('Command description')]
class SendRenewalReminders extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
    }
}
