<?php

namespace App\Http\Controllers\Files\Api;

use App\Http\Controllers\Controller;
use App\Models\File;
// use App\Traits\canGetOAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class Store extends Controller
{
  // use canGetOAuth;
  /**
   * Handle the incoming request.
   */
  public function __invoke(Request $request)
  {
    if ($request->hasFile('file')) {

      /*$redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];

      $client = new \Google\Client();

      $client->setAccessToken($this->oAuthToken());

      $client->setRedirectUri($redirect_uri);

      $client->addScope(\Google\Service\Drive::DRIVE);

      $client->setAccessType('offline');*/

      $file = $request->file('file');

      $path = Storage::disk('files')->putFile($request->user()->id . '/' . now()->format('Y') . '/' . now()->format('m'), $request->file('file'));

      $media = File::create([

        'filename' => $file->hashName(),

        'mime_type' => $file->getMimeType(),

        'size' => $file->getSize(),

        'file_path' => $path,

        'user_id' => $request->user()->id

      ]);

      /*$path = $request->file->getRealPath();
      $filename = now()->format('Y') . '_' . now()->format('m') . '_' . $file->hashName();
      // $photo = $photo = fopen($path, 'r'); // use a stream instead

      // upload files now
      $drive = new \Google\Service\Drive($client);

      $fileMetadata = new \Google\Service\Drive\DriveFile(array(
        'name' => $filename,
        'parents' => [config('services.google.comment_folder_id')],
      ));

      // $mediaUpload->setName();
      $mediaUpload = file_get_contents($path);

      // ?uploadType=multipart
      $resp = $drive->files->create($fileMetadata,
        [
          'data' => $mediaUpload,
          'mimeType' => $file->getMimeType(),
          'uploadType' => 'multipart'
        ]
      );*/

      return response()->json([
        'id' => $media->id
      ]);

    }

  }
}
