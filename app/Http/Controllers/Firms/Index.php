<?php

namespace App\Http\Controllers\Firms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Index extends Controller
{
  // public function __invoke(Request $request)
  // {
  //   $companies = Company::query()
  //     ->when($request->q, fn ($query, $q) => $query->where('name', 'LIKE', "%{$q}%")->pluck('name', 'id')->transform(function ($name, $id) {
  //       return [
  //         'label' => $name,
  //         'value' => $id,
  //       ];
  //     }))
  //     ->when(!$request->q, fn ($query) => $query->pluck('name', 'id')->transform(function ($name, $id) {
  //       return [
  //         'label' => $name,
  //         'value' => $id,
  //       ];
  //     }));

  //   return response()->json($companies);
  // }

  public function __invoke(Request $request)
  {
    /*$companies = Company::query()
      ->when($request->q, function ($query, $q) {
        return $query->where('name', 'LIKE', "%{$q}%");
      })
      ->get(['id', 'name'])
      ->map(function ($company) {
        return [
          'label' => $company->name,
          'value' => $company->id,
        ];
      });*/

    $companiesQuery = \App\Models\Firm::query()->orderBy('name');

    if ($request->q) {
      $companiesQuery->where('name', 'LIKE', "%{$request->q}%");
    }

    $companies = $companiesQuery->get(['fid', 'name'])
      ->map(function ($company) {
        return [
          'name' => $company->name,
          'fid' => $company->fid,
        ];
      });

    return response()->json($companies);
  }
}
