<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('verifier_institutions', function (Blueprint $table) {
            $table->id();
            $table->string('institution_id')->nullable();
            $table->string('company_id')->nullable();
            $table->string('verified_admin_id');
            $table->string('verifier_status');
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
        Schema::dropIfExists('verifier_institutions');
    }
};
