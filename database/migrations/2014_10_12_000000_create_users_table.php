<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /* Users with category_id that matches as organizations can create users that can access the company files.
    * users with user_company_id are staffs of the comapany whose company_id that they have.
     company_ref is the link that will be given by schools to get users to register under them
    */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('firstName');
            $table->string('lastName');
            $table->string('phone');
            $table->string('status')->nullable();
            $table->string('company_ref')->nullable();
            $table->bigInteger('user_company_id')->nullable();
            $table->bigInteger('created_by_user_id')->nullable();
            $table->BigInteger('category_id')->nullable();
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
