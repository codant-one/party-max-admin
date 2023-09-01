<?php

namespace App\Http\Controllers\Testing;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use App\Models\UserStat;
use App\Services\OpengraphService;
use App\Services\AwinService;

class TestingController extends Controller
{
    public function permissions()
    {
        $permissions = Permission::all();

        return response()->json([
            'success' => true,
            'data' => $permissions
        ], 200);
    }

}
