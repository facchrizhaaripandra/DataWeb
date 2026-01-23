<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsPublicToDatasetsTable extends Migration
{
    public function up()
    {
        Schema::table('datasets', function (Blueprint $table) {
            $table->boolean('is_public')->default(false)->after('description');
            $table->enum('access_type', ['private', 'public', 'shared'])->default('private')->after('is_public');
        });
    }

    public function down()
    {
        Schema::table('datasets', function (Blueprint $table) {
            $table->dropColumn(['is_public', 'access_type']);
        });
    }
}