<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->string('titulo');
            $table->string('nome_paciente')->nullable();
            $table->string('nome_medico')->nullable();
            $table->string('tipo_documento')->nullable();
            $table->date('data_documento')->nullable();
            $table->boolean('is_processing')->default(false); // indica se o documento está sendo processado pela IA
            $table->string('caminho_arquivo');
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
