<?php

namespace App\Http\Controllers\Files\Api;

use App\Http\Controllers\Controller;
use App\Models\File;
use Illuminate\Support\Facades\Storage;

class Destroy extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(File $file)
    {
        Storage::disk('public')
          ->delete('files/' . $file->user_id . '/' . now()->format('Y') . '/' . now()->format('m') . '/' . $file->filename);

        $file->delete();

        return response()->json([
          'message' => 'Ok'
        ]);
    }
}
