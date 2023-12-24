<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * The id refers to the primary key of the document, he verifier name refers to the name of the organization to verify the document.
     * The verifier id refers to the id of the verifying comapany in the comapanies table
     * The document name refers to the name of the document and can be certificates, etc.
     * The status refers to the stage of the document in the verifying line which can verified etc
     * In the event where a company verifies a  document, then that company can access all documents its verified using company_viewer_id
     * @return void
     */
    public function up()
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('document_name');
            $table->bigInteger('document_user_id');
            $table->string('document_verifier_name');
            $table->bigInteger('document_verifier_id');
            $table->bigInteger('company_viewer_id')->nullable();
            $table->string('document_type');
            $table->string('document_status');
            $table->string('document_path');
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
        Schema::dropIfExists('documents');
    }
};
