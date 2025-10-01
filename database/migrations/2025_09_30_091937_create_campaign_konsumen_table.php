<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampaignKonsumenTable extends Migration
{
    public function up()
    {
        Schema::create('campaign_konsumen', function (Blueprint $table) {
            $table->primary(['campaign_id', 'konsumen_id']);
            $table->foreignId('campaign_id')->constrained('campaigns')->onDelete('cascade');
            $table->foreignId('konsumen_id')->constrained('konsumens')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('campaign_konsumen');
    }
}
