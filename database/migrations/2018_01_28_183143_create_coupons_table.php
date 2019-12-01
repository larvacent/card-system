<?php
use Illuminate\Support\Facades\Schema; use Illuminate\Database\Schema\Blueprint; use Illuminate\Database\Migrations\Migration; class CreateCouponsTable extends Migration { public function up() { Schema::create('coupons', function (Blueprint $sp9906b2) { $sp9906b2->increments('id'); $sp9906b2->integer('user_id')->index(); $sp9906b2->integer('category_id')->default(-1); $sp9906b2->integer('product_id')->default(-1); $sp9906b2->integer('type')->default(\App\Coupon::TYPE_REPEAT); $sp9906b2->integer('status')->default(\App\Coupon::STATUS_NORMAL); $sp9906b2->string('coupon', 100)->index(); $sp9906b2->integer('discount_type'); $sp9906b2->integer('discount_val'); $sp9906b2->integer('count_used')->default(0); $sp9906b2->integer('count_all')->default(1); $sp9906b2->string('remark')->nullable(); $sp9906b2->dateTime('expire_at')->nullable(); $sp9906b2->timestamps(); }); } public function down() { Schema::dropIfExists('coupons'); } }