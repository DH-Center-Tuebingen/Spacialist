<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Context;

class FixRanks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $rootContexts = Context::whereNull('root_context_id')
            ->orderBy('rank')
            ->get();

        $rootCtr = 1;
        foreach($rootContexts as $rc) {
            $rc->rank = $rootCtr++;
            $childCtr = 1;
            $childContexts = Context::where('root_context_id', $rc->id)
                ->orderBy('rank')
                ->get();
            foreach($childContexts as $cc) {
                $cc->rank = $childCtr++;
                $cc->save();
            }
            $rc->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Nothing to do here
    }
}
