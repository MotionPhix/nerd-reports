<?php

namespace App\Http\Controllers\Files\Api;

use App\Http\Controllers\Controller;
use App\Models\File;
use Illuminate\Http\Request;

class Store extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
      $request->dd();

      if ($request->hasFile('file')) {

        $file = $request->file('file');

        // $file->store('files/' . $request->user()->id . '/' . now()->format('Y') . '/' . now()->format('m'), 'public');
        $file->store('files/' . $request->user()->id . '/' . now()->format('Y') . '/' . now()->format('m'), 'public');

        $media = File::create([

          'filename' => $file->hashName(),

          'mime_type' => $file->getMimeType(),

          'size' => $file->getSize(),

          'user_id' => $request->user()->id

        ]);

        return response()->json([
          'id' => $media->id
        ]);

      }

    }
}
