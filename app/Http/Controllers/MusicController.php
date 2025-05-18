<?php

namespace App\Http\Controllers;

use App\Models\Music;
use Exception;
use getID3;
use getid3_lib;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Native\Laravel\Dialog;
use Symfony\Component\HttpFoundation\BinaryFileResponse;


class MusicController extends Controller
{

    /**
     * @return RedirectResponse
     * @throws Exception
     */
    public function pickFileOrFolder()
    {
        $result = Dialog::new()
            ->title('Select Music Files')
            ->properties(['openFile', 'multiSelections'])
            ->filter('Audio Files', ['mp3', 'wav', 'flac', 'ogg', 'aac'])
            ->open();

        if (empty($result)) {
            return redirect()->back()->with('error', 'No selection made.');
        }

        foreach ($result as $path) {
            if (is_dir($path)) {
                $this->scanAndStore($path);
            } else {
                $this->storeMusicFile($path);
            }
        }

        return redirect()->route('music.index')->with('success', 'Music files imported!');
    }


    /**
     * @param string $dir
     * @return void
     * @throws Exception
     */
    private function scanAndStore(string $dir)
    {
        $files = File::allFiles($dir);
        foreach ($files as $file) {
            if (in_array($file->getExtension(), ['mp3', 'wav', 'flac', 'ogg', 'aac'])) {
                $this->storeMusicFile($file->getRealPath());
            }
        }
    }


    /**
     * @param string $originalPath
     * @return void
     */
    private function storeMusicFile(string $originalPath)
    {
        $filename = basename($originalPath);
        $uniqueName = uniqid() . '_' . $filename;
        $destination = storage_path('app/public/music/' . $uniqueName);

        // Ensure directory exists & copy file
        File::ensureDirectoryExists(dirname($destination));
        File::copy($originalPath, $destination);

        // Analyze metadata
        $getID3 = new getID3();
        $fileInfo = $getID3->analyze($destination);
        getid3_lib::CopyTagsToComments($fileInfo);

        $title = $fileInfo['comments_html']['title'][0] ?? null;
        $artist = $fileInfo['comments_html']['artist'][0] ?? null;
        $album = $fileInfo['comments_html']['album'][0] ?? null;
        $duration = isset($fileInfo['playtime_seconds']) ? (int)round($fileInfo['playtime_seconds']) : null;

        $coverImagePath = null;
        if (!empty($fileInfo['id3v2']['APIC'][0]['data'])) {
            $imageData = $fileInfo['id3v2']['APIC'][0]['data'];
            $imageExt = $fileInfo['id3v2']['APIC'][0]['image_mime'] === 'image/png' ? 'png' : 'jpg';
            $coverFilename = Str::random(12) . '.' . $imageExt;
            $coverImagePath = 'covers/' . $coverFilename;

            File::ensureDirectoryExists(storage_path('app/public/covers'));
            File::put(storage_path('app/public/' . $coverImagePath), $imageData);
        }

        // Save record
        Music::create([
            'name' => $filename,
            'path' => 'music/' . $uniqueName,
            'title' => $title,
            'artist' => $artist,
            'album' => $album,
            'duration' => $duration,
            'cover_image' => $coverImagePath,
        ]);
    }

    /**
     * @return Factory|View|Application|\Illuminate\View\View|object
     */
    public function index()
    {
        $musicFiles = Music::latest()->get();
        return view('music.index', compact('musicFiles'));
    }

    /**
     * @param $file
     * @return BinaryFileResponse
     */
    public function stream($file)
    {
        $path = 'music/' . $file;

        if (!Storage::exists($path)) {
            abort(404);
        }

        return response()->file(storage_path('app/' . $path));
    }
}
