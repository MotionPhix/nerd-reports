<?php

namespace App\Http\Controllers\Files;

use App\Http\Controllers\Controller;
use App\Models\File;
use Illuminate\Support\Facades\Storage;
// use Google\Client;
// use Google\Service\Drive;

class Download extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(File $file)
  {

    $headers = [
      'Content-Type' => $file->mime_type,
    ];

    /*$client = new Client();

    $client->useApplicationDefaultCredentials();
    $client->addScope(Drive::DRIVE);
    $driveService = new Drive($client);

    $response = $driveService->files->get($file, array(
      'alt' => 'media')
    );

    $content = $response->getBody()->getContents();

    return $content;*/

    return Storage::disk('files')->download(path: $file->file_path, headers: $headers);

  }
}
