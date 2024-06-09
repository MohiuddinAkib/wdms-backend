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
            $table->id();
            $table->uuid('uuid')->index();
            $table->foreignUuid('user_id')->constrained(table: User::getModel()->getTable(), column: 'uuid');
            $table->string('currency');
            $table->decimal('balance', 64, 0)
                ->default(0);
            $table->unsignedSmallInteger('decimal_places')
                ->default(2);
            $table->timestamps();
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
