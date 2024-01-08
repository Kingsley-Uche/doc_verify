<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This table contains the information about the owners of the document to be verified
     * docOwnerFirstName,docOwnerMiddleName and docOwnerLastName refers the firstname, middle name and lastname of the document owner
     * docOwnerDob refers to the date of birth of the document owner
     *
     * @return void
     */
    public function up()
    {
        Schema::create('document_owners', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('docOwnerFirstName');
            $table->string('docOwnerMiddleName')->nullable();
            $table->string('docOwnerLastName');
            $table->string('docOwnerDOB');
            $table->string('uploaded_by_user_id');
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
        Schema::dropIfExists('document_owners');
    }
};
