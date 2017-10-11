<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class VersionController extends Controller
{
    public function bluetooth()
    {
        $result = DB::select('select version,downloadAddressA,downloadAddressB from BluetoothVersions');
        if($result){
            return response()->json($result[0],200);
        }else{
            return response()->json(null,404);
        }
    }
}