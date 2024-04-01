<?php

namespace App\Http\Controllers\Contacts;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;

class Destroy extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke($cids)
    {
      $idsArray = explode(',', $cids);

      Contact::whereIn('cid', $idsArray)->delete();

      return redirect()->route('contacts.index');
    }
}
