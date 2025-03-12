<?php

namespace App\Console\Commands;

use App\EntityAttribute;

use Illuminate\Console\Command;

class RestructureDependsOn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:restructure-depends-on';

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
        //
        
        $results = EntityAttribute::where('depends_on', '!=', null)->get();
        
        foreach($results as $result) {            
            $dependsOn = $result->depends_on;
            if(isset($dependsOn['union'])){
                $dependsOn['is_and'] = !$dependsOn['union'];
                unset($dependsOn['union']);
            }
            
            if(isset($dependsOn['groups'])){
                foreach($dependsOn['groups'] as $key => $group) {
                    if(isset($group['union'])){
                        $dependsOn['groups'][$key]['is_and'] = !$group['union'];
                        unset($dependsOn['groups'][$key]['union']);
                    }  
                }
            }
           
            $result->depends_on = $dependsOn;
            $result->save();
        }
    }
}
