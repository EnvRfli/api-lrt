<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id(); // Primary key, auto-increment
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Foreign key to users.id
            $table->enum('program_name', ['PKH', 'BLT', 'Bansos'])->notNullable(); // Program name
            $table->string('province_code', 50)->notNullable(); // Province code from external API
            $table->string('district_code', 50)->notNullable(); // District code from external API
            $table->string('subdistrict_code', 50)->notNullable(); // Subdistrict code from external API
            $table->integer('recipient_count')->notNullable(); // Number of recipients
            $table->date('distribution_date')->notNullable(); // Distribution date
            $table->string('distribution_proof', 255)->notNullable(); // Path to distribution proof file
            $table->text('notes')->nullable(); // Additional notes
            $table->enum('status', ['Pending', 'Disetujui', 'Ditolak'])->default('Pending')->notNullable(); // Report status
            $table->text('rejection_reason')->nullable(); // Rejection reason (only if rejected)
            $table->timestamps(); // created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reports');
    }
};
