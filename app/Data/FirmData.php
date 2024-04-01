<?php

namespace App\Data;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

/** @typescript * */
class FirmData extends Data
{
  public function __construct(
    public int|Optional              $id,

    public string|Optional           $fid,

    public string|null|Optional      $slogan,

    public AddressData|null|Optional $address,

    public string|null|Optional      $url,

    public string|null|Optional      $name,

    /** @var Collection<TagData> */
    public Collection|null|Optional  $tags,
  ) {}
}
