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
        Schema::table('denominations', function (Blueprint $table) {
            $table->foreignUuid('wallet_id')->constrained('wallets', 'uuid');

            $table->unique(['wallet_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('denominations', function (Blueprint $table) {
            $table->dropForeign('wallet_id');
        });
    }
};
