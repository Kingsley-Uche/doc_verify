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
        Schema::create('educational_documents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('doc_owner_id');
            $table->string('country_code');
            $table->string('studentId');//matric number
            $table->string('date_of_issue');
            $table->string('exam_board');
            $table->string('verifier_name');
            $table->bigInteger('verifier_id')->nullable();
            $table->string('verifier_city');
            $table->string('start_year');
            $table->string('end_year');
            $table->string('course');
            $table->longText('doc_path');
            $table->longText('ref_id');
            $table->longText('doc_info');
            $table->longText('application_id')->nullable();
            $table->string('status');
            $table->string('doc_type')->default('educ');
            $table->string('uploaded_by_user_id');
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
        Schema::dropIfExists('educational_documents');
    }
};
