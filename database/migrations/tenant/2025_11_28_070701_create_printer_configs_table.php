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
        Schema::create('printer_configs', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., 'Kitchen', 'Bar', 'Receipt'
            $table->string('type'); // 'kitchen', 'bar', 'receipt'
            $table->string('bluetooth_name')->nullable(); // Bluetooth device name
            $table->string('bluetooth_address')->nullable(); // Bluetooth MAC address
            $table->string('service_uuid')->default('000018f0-0000-1000-8000-00805f9b34fb');
            $table->string('characteristic_uuid')->default('00002af1-0000-1000-8000-00805f9b34fb');
            $table->enum('paper_size', ['36mm', '76mm'])->default('76mm');
            $table->integer('character_width')->default(48); // Characters per line
            $table->boolean('is_active')->default(true);
            $table->boolean('auto_cut')->default(true);
            $table->integer('cut_spacing')->default(5); // Line feeds before cut
            $table->json('print_categories')->nullable(); // Which categories to print (for kitchen/bar)
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('printer_configs');
    }
};
