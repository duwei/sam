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
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('token_type');
            $table->dropColumn('expires_in');
            $table->dropColumn('access_token');
            $table->dropColumn('refresh_token');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->string('token_type')->nullable();
            $table->string('expires_in')->nullable();
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->string('token_type')->nullable();
            $table->string('expires_in')->nullable();
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('token_type');
            $table->dropColumn('expires_in');
            $table->dropColumn('access_token');
            $table->dropColumn('refresh_token');
        });
    }
};
