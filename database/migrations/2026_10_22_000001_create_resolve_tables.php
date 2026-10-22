<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('users', fn(Blueprint $t) => $t->string('role')->default('student'));
        Schema::create('categories', function(Blueprint $t) {
            $t->id(); $t->string('name')->unique(); $t->text('description')->nullable(); $t->timestamps();
        });
        Schema::create('complaints', function(Blueprint $t) {
            $t->id(); $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('category_id')->constrained()->restrictOnDelete();
            $t->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $t->string('title'); $t->text('description'); $t->string('location');
            $t->string('priority')->default('Normal'); $t->string('status')->default('Open');
            $t->timestamp('resolved_at')->nullable(); $t->timestamps();
        });
        Schema::create('comments', function(Blueprint $t) {
            $t->id(); $t->foreignId('complaint_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete(); $t->text('body'); $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('comments'); Schema::dropIfExists('complaints'); Schema::dropIfExists('categories');
        Schema::table('users', fn(Blueprint $t) => $t->dropColumn('role'));
    }
};
