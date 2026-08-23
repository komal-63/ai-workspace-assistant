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
        Schema::table('document_chunks', function (Blueprint $table) {
            $table->unique(
                ['document_id', 'chunk_index'],
                'document_chunks_document_id_chunk_index_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       Schema::table('document_chunks', function (Blueprint $table) {
            $table->dropUnique(
                'document_chunks_document_id_chunk_index_unique'
            );
        });
    }
};
