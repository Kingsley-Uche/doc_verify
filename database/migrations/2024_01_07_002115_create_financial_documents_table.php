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
        Schema::create('financial_documents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('doc_owner_id');
            $table->string('bank_name');
            $table->longText('description')->nullable();
            $table->string('country_code');
            $table->longText('doc_path');
            $table->longText('ref_id');
            $table->longText('viewer_code')->nullable();
            $table->string('uploaded_by_id');
            $table->string('status');
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
        Schema::dropIfExists('financial_documents');
    }
};
