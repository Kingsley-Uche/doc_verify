<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /* Users with category_id that matches as organizations can create users that can access the company files.
    * users with user_company_id are staffs of the comapany whose company_id that they have.
    */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('firstName');
            $table->string('lastName');
            $table->string('phone');
            $table->bigInteger('user_company_id')->nullable();
            $table->bigInteger('created_by_user_id')->nullable();
            $table->BigInteger('category_id');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}
