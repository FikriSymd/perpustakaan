<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('title', 255);
            $table->string('slug', 255)->unique();
            $table->string('author', 150);
            $table->string('publisher', 150)->nullable();
            $table->unsignedSmallInteger('publication_year')->nullable();
            $table->string('isbn', 20)->nullable()->unique();
            $table->unsignedInteger('stock_total')->default(1);
            $table->unsignedInteger('stock_available')->default(1);
            $table->string('cover_path')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('title');
            $table->index('author');
            $table->index(['category_id', 'title']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
