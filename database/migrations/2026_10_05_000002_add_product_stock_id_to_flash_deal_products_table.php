<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('flash_deal_products', function (Blueprint $table) {
            $table->unsignedBigInteger('product_stock_id')->nullable()->after('product_id');
        });
    }

    public function down()
    {
        Schema::table('flash_deal_products', function (Blueprint $table) {
            $table->dropColumn('product_stock_id');
        });
    }
};
