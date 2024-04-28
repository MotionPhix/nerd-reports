<?php

namespace App\Traits;

use Google\Client;
use Google\Service\Drive;

trait canCreateFolder
{
  public function createFolder()
  {
    $client = new Client();

    $client->useApplicationDefaultCredentials();

    $client->addScope(Drive::DRIVE);

    $driveService = new Drive($client);

    $fileMetadata = new Drive\DriveFile([
      'name' => 'Invoices',
      'mimeType' => 'application/vnd.google-apps.folder'
    ]);

    $file = $driveService->files->create($fileMetadata, ['fields' => 'id']);

    return $file->id;
  }
}
