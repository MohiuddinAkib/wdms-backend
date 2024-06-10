<?php

use App\Models\User;
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
        Schema::create('wallets', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->foreignUuid('user_id')->constrained(table: User::getModel()->getTable(), column: 'uuid');
            $table->string('currency');
            $table->string('balance')
                ->default('0');
            $table->timestamps();

            // DATABASE LEVEL CHECK FOR USER WALLETS WITH UNIQUE CURRENCY
            $table->unique(['user_id', 'currency']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
