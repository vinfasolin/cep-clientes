<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table): void {
            $table->id();
            $table->string('nome', 120);
            $table->string('email', 160)->unique();
            $table->string('cep', 9);
            $table->string('logradouro', 180);
            $table->string('numero', 20);
            $table->string('complemento', 120)->nullable();
            $table->string('bairro', 120);
            $table->string('cidade', 120);
            $table->char('uf', 2);
            $table->timestamps();

            $table->index('cep');
            $table->index(['cidade', 'uf']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
