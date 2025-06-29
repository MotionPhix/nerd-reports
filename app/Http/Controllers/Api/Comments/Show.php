<?php

namespace App\Http\Controllers\Api\Comments;

use App\Http\Controllers\Controller;

//use App\Traits\canGetOAuth;
//use Google\Client;
//use Google\Service\Drive;

class Show extends Controller
{
  // use canGetOAuth;

  /**
   * Handle the incoming request.
   */
  public function __invoke()
  {
    /*try {

      $client = new Client();

      $client->setAccessToken($this->oAuthToken());

      $client->addScope(Drive::DRIVE);

      $driveService = new Drive($client);

      $folderId = config('services.google.comment_folder_id');

      $response = $driveService->files->listFiles(array(
        'corpora' => "drive",
        'fields' => 'files(id, name)',
      ));

      foreach ($response->files as $file) {
        printf("Found file: %s (%s)", $file->name, $file->id);
      }

      return $response->files;

    } catch (\Exception $e) {
      echo "Error Message: " . $e;
    }
    */
  }

}
