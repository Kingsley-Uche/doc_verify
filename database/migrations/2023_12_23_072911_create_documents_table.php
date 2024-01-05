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
     * document_owner_id  refers to the unique identity of the owner in doc_owner_table;
     * document_ref_code is the unique code the owner can share to a third party to access the document on this portal
     * @return void
     */
    public function up()
    {




        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('document_category');
            $table->bigInteger('document_owner_id');
            $table->string('document_verifier_name');
            $table->bigInteger('document_verifier_id')->nullable();
            $table->string('document_verifier_city')->nullable();
            $table->bigInteger('doc_verifier_country');
            $table->bigInteger('document_viewer_id')->nullable();
            $table->string('document_status');
            $table->longText('document_ref_code');
           $table->string('document_qualification');
            $table->string('doc_info')->nullable();
            $table->string('doc_matric_number')->nullable();
            $table->string('doc_start_year')->nullable();
            $table->string('doc_end_year')->nullable();
            $table->string('doc_dateOfIssue')->nullable();
            $table->string('doc_course')->nullable();
            $table->string('enrollment_status')->nullable();
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
