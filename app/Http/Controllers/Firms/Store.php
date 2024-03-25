<?php

namespace App\Http\Controllers\Firms;

use App\Data\FirmData;
use App\Http\Controllers\Controller;
use App\Models\Firm;

class Store extends Controller
{
  public function __invoke(FirmData $firmData)
  {
    $validated = $firmData->toArray();

    $firm = Firm::create($validated);

    return response()->json(FirmData::from($firm));
  }
}
