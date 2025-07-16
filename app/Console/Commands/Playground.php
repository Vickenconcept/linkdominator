<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Uri;
use Illuminate\Support\Facades\URL;
use App\Services\LinkedInService;
use App\Models\User;
use DB;

class Playground extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:playground';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::whereNot('id', 1)->get();

        foreach ($users as $user) {
            // DB::select("insert into model_has_roles (role_id, model_type, model_id) values (2, 'App\\Models\\User', $user->id)");
            
        }
    }
}
