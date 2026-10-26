<?php

namespace Tests\Feature;

use App\Providers\NativeAppServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * An installed app has no artisan step before its first window: nothing
 * creates the sqlite file and nothing applies the schema. A packaged build
 * also never has `app.debug` on, which is the only state where NativePHP
 * would have done it itself.
 *
 * These tests deliberately work on throwaway files instead of the shared
 * in-memory database, because that is the situation being reproduced.
 */
class NativeFirstBootTest extends TestCase
{
    protected string $scratch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->scratch = sys_get_temp_dir().'/laratools-boot-'.Str::random(10);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->scratch)) {
            $items = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($this->scratch, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST,
            );

            foreach ($items as $item) {
                $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
            }

            rmdir($this->scratch);
        }

        parent::tearDown();
    }

    /**
     * Point the default connection at a file that does not exist yet, in a
     * directory that does not exist either.
     */
    protected function useFileDatabase(string $path): void
    {
        config([
            'database.connections.nativeboot' => [
                'driver' => 'sqlite',
                'url' => null,
                'prefix' => '',
                'foreign_key_constraints' => true,
                'database' => $path,
            ],
            'database.default' => 'nativeboot',
        ]);

        DB::purge('nativeboot');
    }

    public function test_the_database_file_is_created_where_none_exists(): void
    {
        $path = "{$this->scratch}/nested/app.sqlite";

        $this->useFileDatabase($path);

        (new NativeAppServiceProvider($this->app))->prepareDatabase();

        $this->assertFileExists($path);
    }

    public function test_a_fresh_database_ends_up_with_the_current_schema(): void
    {
        $path = "{$this->scratch}/app.sqlite";

        $this->useFileDatabase($path);

        (new NativeAppServiceProvider($this->app))->prepareDatabase();

        foreach (['settings', 'notes', 'todos', 'events', 'music', 'cities'] as $table) {
            $this->assertTrue(Schema::connection('nativeboot')->hasTable($table), "Missing table: {$table}");
        }
    }

    public function test_booting_twice_keeps_the_data_and_adds_nothing(): void
    {
        $path = "{$this->scratch}/app.sqlite";

        $this->useFileDatabase($path);

        $provider = new NativeAppServiceProvider($this->app);

        $provider->prepareDatabase();

        DB::connection('nativeboot')->table('settings')->insert([
            'key' => 'appearance',
            'value' => 'light',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // The second launch is the ordinary case, not the first one.
        $provider->prepareDatabase();

        $this->assertSame(
            'light',
            DB::connection('nativeboot')->table('settings')->where('key', 'appearance')->value('value'),
        );
        // Still one batch: the second launch found nothing to apply, so it
        // neither re-ran the schema nor rewrote the rows.
        $this->assertSame(1, (int) DB::connection('nativeboot')->table('migrations')->max('batch'));
    }
}
