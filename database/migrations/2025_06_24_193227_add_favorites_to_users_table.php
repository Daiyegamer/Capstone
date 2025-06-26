<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
  public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        for ($i = 1; $i <= 3; $i++) {
            $table->string("favorite{$i}_name")->nullable();
            $table->string("favorite{$i}_place_id")->nullable();
        }
    });
}


    /**
     * Reverse the migrations.
     */
public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        for ($i = 1; $i <= 3; $i++) {
            $table->dropColumn("favorite{$i}_name");
            $table->dropColumn("favorite{$i}_place_id");
        }
    });
}

};
