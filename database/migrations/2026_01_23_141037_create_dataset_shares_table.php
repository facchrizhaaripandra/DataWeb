<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDatasetSharesTable extends Migration
{
    public function up()
    {
        Schema::create('dataset_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dataset_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('shared_by')->constrained('users')->onDelete('cascade');
            $table->enum('permission', ['view', 'edit', 'owner'])->default('view');
            $table->timestamps();
            
            $table->unique(['dataset_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('dataset_shares');
    }
}