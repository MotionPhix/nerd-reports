<?php

namespace App\Traits;

use Google\Client;
use Google\Service\Drive;
use Illuminate\Support\Facades\Http;

trait canGetOAuth
{
  private function oAuthToken()
  {
    $resp = Http::post('https://oauth2.googleapis.com/token', [
      'client_id' => config('services.google.client_id'),
      'client_secret' => config('services.google.client_secret'),
      'refresh_token' => config('services.google.refresh_token'),
      'grant_type' => 'refresh_token'
    ]);

    $token = json_decode((string)$resp->getBody(), true)['access_token'];

    return $token;
  }
}
