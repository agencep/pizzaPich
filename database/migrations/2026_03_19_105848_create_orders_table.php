<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $blade) {
            $blade->id();
            $blade->decimal('total', 15, 3); // 3 decimals for TND
            $blade->decimal('received', 15, 3);
            $blade->decimal('change', 15, 3);
            $blade->json('items');
            $blade->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
