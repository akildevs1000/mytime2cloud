<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SDKController extends Controller
{
    // $url = "https://sdk.ideahrms.com";

    protected $endpoint = "http://localhost:5000";

    public function processTimeGroup(Request $request, $id)
    {
        $url = "{$this->endpoint}/{$id}/WriteTimeGroup";

        return $this->processSDKRequest($url, $request);
    }

    public function PersonAddRange(Request $request)
    {
        $url = "{$this->endpoint}/Person/AddRange";

        return $this->processSDKRequest($url, $request);
    }

    public function processSDKRequest($url, $request)
    {
        try {
            return Http::withoutVerifying()->withHeaders([
                'Content-Type' => 'application/json',
            ])->post($url, $request->all());
        } catch (\Throwable $th) {
            return  $th->getMessage();
        }
    }
}
