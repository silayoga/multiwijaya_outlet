<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pricing_plans', function (Blueprint $table) {
            // Stable, URL-safe identifier external sites can put in a checkout
            // link (?plan=restoflow-basic) — unlike plan_name, this never
            // changes once published, so links stay valid across renames.
            $table->string('slug')->nullable()->unique()->after('plan_name');
        });
    }

    public function down(): void
    {
        Schema::table('pricing_plans', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
