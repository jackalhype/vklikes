<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use DB;

/**
 * Our DB enlarges in a crazy manner.
 * So we clear it out every hour or close to that.
 * tables: vklikes, vk_users
 */
class ClearDB extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     * Delete records from vklikes, vk_users, older than 5 hours
     *
     * @return void
     */
    public function handle()
    {
        $request_ids_query = DB::table('vklikes_requests')
            ->select('vklikes_requests.id')
            ->join('vklikes', 'vklikes_requests.id', '=', 'vklikes.vklikes_request_id')
            ->whereRaw('updated_at < now() - INTERVAL 5 HOUR');        
        $sql = $request_ids_query->toSql();
        $max_request_id = $request_ids_query->max('vklikes_requests.id');
        // throw new \Exception(print_r($max_request_id, true));
        // throw new \Exception(print_r($sql, true));
        if ($max_request_id) {
            DB::table('vklikes')->where('vklikes_request_id', '<=', $max_request_id)->delete();
        }
        DB::table('vk_users')->whereRaw('updated_at < now() - INTERVAL 5 HOUR')->delete();
    }
}
