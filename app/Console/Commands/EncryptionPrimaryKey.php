<?php

namespace App\Console\Commands;

use App\Librarys\Librarys_;
use App\Models\CachePrimaryKeyEncryption;
use App\Models\Queues;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use PhpParser\Node\Expr\Print_;

class EncryptionPrimaryKey extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = COMMAND_GET_KEY;

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
        $array = [];
        $queryService = Queues::get();

        $queryMyService = Queues::where([FIELD_NAME => FIELD_MY_SERVICE])->get();
        $myservice = $queryMyService->value(FIELD_CODE);
        foreach($queryService as $service){
            if ($service[FIELD_NAME]!=FIELD_MY_SERVICE){
                $url = $service[FIELD_END_POINT];
                $keys = Librarys_::callApiKeys($url, $myservice);
                try {
                    $model = new CachePrimaryKeyEncryption($keys,
                        $service[FIELD_CODE],
                        Librarys_::getDate(),
                        $service[FIELD_NAME],
                        $service[FIELD_END_POINT]
                    );
                    array_push($array,$model);
                }catch (Exception $exception){
                    echo "error";
                }
            }
        }

        if ($queryService && $queryMyService){
            if (Cache::has(KEY_CACHE_PRIMARY_KEY_ENCRYPTION)){
                Cache::forget(KEY_CACHE_PRIMARY_KEY_ENCRYPTION);
            }
            Cache::put(KEY_CACHE_PRIMARY_KEY_ENCRYPTION,json_encode([
                FIELD_MY_SERVICE => $myservice,
                FIELD_CACHE => $array
            ]));
            Log::info("Write Cache Succes",$array);
            return Command::SUCCESS;
        }else{
            Log::info("Write Cache Failed failed");
            return Command::FAILURE;
        }

    }

}
