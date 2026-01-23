<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDatasetMergesTable extends Migration
{
    public function up()
    {
        Schema::create('dataset_merges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_dataset_id')->nullable()->constrained('datasets')->onDelete('set null');
            $table->foreignId('target_dataset_id')->constrained('datasets')->onDelete('cascade');
            $table->string('source_type')->default('dataset'); // 'dataset' or 'file'
            $table->string('filename')->nullable(); // if source_type is 'file'
            $table->json('merged_columns')->nullable();
            $table->integer('rows_added')->default(0);
            $table->integer('rows_skipped')->default(0);
            $table->integer('duplicates_removed')->default(0);
            $table->boolean('remove_duplicates')->default(false);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('dataset_merges');
    }
}