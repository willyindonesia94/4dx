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
        // 1. Fetch existing data
        $wigs = DB::table('master_wigs')->get();

        // 2. Re-populate data as JSON arrays BEFORE changing the column type so MySQL JSON constraint doesn't fail
        foreach ($wigs as $wig) {
            $currentVal = $wig->divisi;
            if (!empty($currentVal)) {
                // If it's already a valid json array, json_decode will succeed
                $decoded = json_decode($currentVal, true);
                if (is_array($decoded)) {
                    // Already an array, leave it
                    continue;
                } else {
                    // Convert single string to JSON array containing that string
                    $newVal = json_encode([$currentVal]);
                    DB::table('master_wigs')->where('id', $wig->id)->update(['divisi' => $newVal]);
                }
            }
        }

        // 3. Change column type
        Schema::table('master_wigs', function (Blueprint $table) {
            $table->json('divisi')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Fetch existing data
        $wigs = DB::table('master_wigs')->get();

        // 2. Change column type back to string
        Schema::table('master_wigs', function (Blueprint $table) {
            $table->string('divisi')->nullable()->change();
        });

        // 3. Re-populate data as single string (take first element of array)
        foreach ($wigs as $wig) {
            $currentVal = $wig->divisi;
            if (!empty($currentVal)) {
                $decoded = json_decode($currentVal, true);
                if (is_array($decoded) && count($decoded) > 0) {
                    $newVal = $decoded[0];
                    DB::table('master_wigs')->where('id', $wig->id)->update(['divisi' => $newVal]);
                }
            }
        }
    }
};
