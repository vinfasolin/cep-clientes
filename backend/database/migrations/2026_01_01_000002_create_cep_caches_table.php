<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cep_caches', function (Blueprint $table): void {
            $table->id();
            $table->string('cep', 8)->unique();
            $table->string('logradouro', 180);
            $table->string('bairro', 120)->nullable();
            $table->string('cidade', 120);
            $table->char('uf', 2);
            $table->json('raw_response')->nullable();
            $table->timestamps();

            $table->index(['cidade', 'uf']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cep_caches');
    }
};
