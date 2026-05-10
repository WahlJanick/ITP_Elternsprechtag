<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_classes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 20)->unique();
        });

        DB::table('school_classes')->insert($this->defaultClasses());
    }

    public function down(): void
    {
        Schema::dropIfExists('school_classes');
    }

    private function defaultClasses(): array
    {
        return collect([
            '1AFME', '1AHET', '1AHIT', '1AHMBA', '1AHWIM', '1BHMBA', '1BHWIM',
            '2AAME', '2AFME', '2AHET', '2AHIT', '2AHMBA', '2AHWIM', '2BHMBA', '2BHWIM',
            '3AAME', '3AFME', '3AHET', '3AHIT', '3AHMBA', '3AHWIM', '3AKME', '3BHMBA', '3BHWIM',
            '4AAME', '4AFME', '4AHET', '4AHIT', '4AHMBA', '4AHWIM', '4AKME', '4BHMBA', '4BHWIM',
            '5AAME', '5AHET', '5AHIT', '5AHMBA', '5AHWIM', '5AKME', '5BHMBA', '5BHWIM',
            '6AAME', '6AKME',
        ])->map(fn (string $name) => ['name' => $name])->all();
    }
};
