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
        Schema::table('users', function (Blueprint $table) {
            $table->integer('third_party_id')->nullable();
            $table->integer('third_party_user_id')->nullable();
            $table->text('third_party_user_info')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('third_party_id');
            $table->dropColumn('third_party_user_id');
            $table->dropColumn('third_party_user_info');
        });
    }
};
