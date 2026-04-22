<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('gm:pre-launch')]
#[Description('Command description')]
class PreLaunchCheck extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
    }
}
