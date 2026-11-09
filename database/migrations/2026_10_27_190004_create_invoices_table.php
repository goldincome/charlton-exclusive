<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create("invoices", function (Blueprint $table) {
            $table->id();
            $table->string("number")->unique();
            $table->foreignId("user_id")->constrained();
            $table->string("invoiceable_type")->nullable();
            $table->unsignedBigInteger("invoiceable_id")->nullable();
            $table->date("issued_at");
            $table->unsignedBigInteger("subtotal")->default(0);
            $table->unsignedBigInteger("vat_amount")->default(0);
            $table->unsignedBigInteger("total")->default(0);
            $table->string("status")->default("unpaid"); // unpaid, paid, refunded, void
            $table->timestamps();
            $table->index(["invoiceable_type", "invoiceable_id"]);
        });
        Schema::create("invoice_line_items", function (Blueprint $table) {
            $table->id();
            $table->foreignId("invoice_id")->constrained()->cascadeOnDelete();
            $table->string("description");
            $table->integer("quantity")->default(1);
            $table->unsignedBigInteger("unit_price");
            $table->unsignedBigInteger("net");
            $table->decimal("vat_rate", 5, 2)->default(0);
            $table->unsignedBigInteger("vat");
            $table->unsignedBigInteger("gross");
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists("invoice_line_items");
        Schema::dropIfExists("invoices");
    }
};