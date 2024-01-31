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
        Schema::create('professional_documents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('doc_owner_id');
            $table->string('course');
            $table->longText('doc_verifier_name');
            $table->bigInteger('doc_verifier_id')->nullable();
            $table->string('country_code');
            $table->string('studentId');
            $table->string('qualification');
            $table->string('enrollment_status');
            $table->string('start_year');
            $table->string('end_year');
            $table->longText('add_info')->nullable();
            $table->longText('doc_path');
            $table->string('uploaded_by_user_id');
            $table->longText('ref_id');
            $table->longText('application_id')->nullable();
            $table->string('status');
            $table->longText('viewer_code')->nullable();


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
        Schema::dropIfExists('professional_documents');
    }
};
