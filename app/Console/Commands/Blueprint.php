<?php

namespace App\Console\Commands;

use App\Services\External\RemoteImageService;
use App\Services\External\ReplicateService;
use Illuminate\Console\Command;

class Blueprint extends Command
{
    protected $signature = 'blueprint';

    protected $description = 'Lorem ipsum dolor sit amet';

    public function handle()
    {
        $this->info('Hello, world!');
    }
}
