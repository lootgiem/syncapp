<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('credential_id')
                ->constrained()
                ->onDelete('cascade');
            $table->string('real_id')
                ->index();
            $table->string('visibility')
                ->nullable();
            $table->string('summary')
                ->nullable();
            $table->string('location')
                ->nullable();
            $table->longText('description')
                ->nullable();
            $table->string('status')
                ->nullable();
            $table->boolean('locked')
                ->nullable();
            $table->boolean('all_day');
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('events');
    }
}
