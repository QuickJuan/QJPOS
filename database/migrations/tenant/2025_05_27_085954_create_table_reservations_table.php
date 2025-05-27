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
        Schema::create('table_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('table_room_id')
                ->constrained('table_rooms')
                ->onDelete('cascade')
                ->comment('Foreign key referencing the table_rooms table');
            $table->unsignedBigInteger('user_id')->nullable()
                ->comment('ID of the user making the reservation');
            $table->dateTime('reservation_from')
                ->comment('Date and time of the reservation');
            $table->datetime('reservation_to')
                ->comment('End date and time of the reservation');
            $table->string('status', 15)->default('active')
                ->comment('Status of the reservation, e.g., active, arrived, cancelled');
            $table->text('notes')->nullable()
                ->comment('Additional notes or comments regarding the reservation');
            $table->integer('pax')->default(1)
                ->comment('Number of people for the reservation');
            $table->string('contact_phone', 25)->nullable()
                ->comment('Contact phone number for the reservation');
            $table->string('contact_email',75)->nullable()
                ->comment('Contact email for the reservation');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_reservations');
    }
};
