<?php

namespace App\Console\Commands;

use App\Librarys\Librarys_;
use App\Models\CachePrimaryKeyEncryption;
use App\Models\Queues;
use App\Models\Services;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use PhpParser\Node\Expr\Print_;

class ResetQueues extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = COMMAND_RESET_QUEUES;

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
    public function handle()
    {
        Queues::truncate();
        Log::debug("Truncate Queue");
    }

}
