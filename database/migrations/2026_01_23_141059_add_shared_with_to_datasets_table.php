<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSharedWithToDatasetsTable extends Migration
{
    public function up()
    {
        Schema::table('datasets', function (Blueprint $table) {
            $table->json('shared_with')->nullable()->after('access_type');
        });
    }

    public function down()
    {
        Schema::table('datasets', function (Blueprint $table) {
            $table->dropColumn('shared_with');
        });
    }
}