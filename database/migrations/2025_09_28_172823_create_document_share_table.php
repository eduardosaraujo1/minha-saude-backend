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
        Schema::table('document_share', function (Blueprint $table) {
            $table->foreignUuid('document_id')
                ->constrained('documents')
                ->cascadeOnDelete();
            $table->foreignUuid('share_id')
                ->constrained('shares')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_share', function (Blueprint $table) {
            //
        });
    }
};
