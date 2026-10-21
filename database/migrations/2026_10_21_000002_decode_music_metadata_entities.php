<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tracks imported before metadata switched to raw tags kept getID3's
     * HTML-escaped comments, so Persian titles landed as numeric entities
     * and apostrophes or ampersands as named ones.
     */
    public function up(): void
    {
        foreach (['title', 'artist', 'album', 'genre'] as $column) {
            $rows = DB::table('music')
                ->whereNotNull($column)
                ->where($column, 'like', '%&%')
                ->get(['id', $column]);

            foreach ($rows as $row) {
                $decoded = html_entity_decode($row->{$column}, ENT_QUOTES, 'UTF-8');

                if ($decoded !== $row->{$column}) {
                    DB::table('music')->where('id', $row->id)->update([$column => $decoded]);
                }
            }
        }
    }

    /**
     * The entities are the damage, not the intent, so there is nothing
     * meaningful to restore.
     */
    public function down(): void {}
};
