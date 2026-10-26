<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->unsignedBigInteger('flash_deal_product_id')->nullable()->after('product_id');
            $table->unsignedBigInteger('discount')->nullable()->after('unit_price');
        });
    }

    public function down()
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->dropColumn('flash_deal_product_id');
            $table->dropColumn('discount');
        });
    }
};
