<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log; // Import Log facade


class OpenBeeController extends Controller
{
    //

    protected string $baseUrl;
    protected string $username;
    protected string $password;
    protected string $getDocument;
    protected string $infoDocument;

	


    public function __construct()
    {
        $this->baseUrl = config('services.openbee.base_url');
        $this->username = config('services.openbee.username');
        $this->password = config('services.openbee.password');
        $this->getDocument = config('services.openbee.get_document');
	//	$this->infoDocument = config('services.openbee.infos_document');


    }

    public function download($id)
    {
      $url = "http://192.168.26.54:8000/ws/v2/file/$id";
       $infos_url="http://192.168.26.54:8000/ws/v2/document/$id";
	   
	   
		/* $url = $this->$baseUrl. $this->$getDocument.$id;

       $infos_url= $this->$baseUrl. $this->$infoDocument.$id;
	   
	    \Log::error("URL: " . $url);
		 \Log::error("INFO URL: " . $infos_url);*/

        $response = Http::withBasicAuth($this->username, $this->password)
                        ->get($url);

        $response_infos = Http::withBasicAuth($this->username, $this->password)
                        ->get($infos_url);

        if ($response->ok()) {
            //$nomFichierChargee = $response_infos->header('name');
            $nomFichierChargee = $response_infos->json('document.name'); // 

            return response($response->body(), 200)
                ->header('Content-Type', $response->header('Content-Type'))
                ->header('Content-Disposition', 'attachment; filename="PJ_'.$nomFichierChargee.'.pdf"');
        }

        return response()->json(['error' => 'File not found'], 404);
    }
}
