<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repository\Contracts\LineGroupInterface;

class LineGroupController extends Controller
{
    //
    protected $configLineGroup;
    public function __construct(LineGroupInterface $configLineGroup)
    {
        $this->configLineGroup = $configLineGroup;
    }

    public function index(Request $request)
    {
        return response()->json($this->configLineGroup->all());
    }

    public function save(Request $request)
    {
        $this->validate($request, ['groupid' => 'required']);

        try {
            $this->configLineGroup->save($request->groupid);
            return response()->json(['error' => false]);
        } catch (Exception $e) {
            return response()->json(['error'=> true, 'message'=> ""]);
        }
    }
}
