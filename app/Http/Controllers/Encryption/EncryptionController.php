<?php

namespace App\Http\Controllers\Encryption;

use App\Http\Controllers\Controller;
use App\Librarys\Encryptions_;
use App\Librarys\Librarys_;
use App\Librarys\ResultRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class EncryptionController extends Controller
{
    public function getPrimaryKey(Request $request){

        $date = Librarys_::getDate();
        $idService = $request->input(FIELD_ID_SERVICE);
        $codeService = $request->input(FIELD_CODE_SERVICE);
        $keys = Encryptions_::encode([
            DATE => $date,
            FIELD_ID_SERVICE => $idService,
            FIELD_CODE => $codeService
        ],$codeService);
        return ResultRequest::exportResultSuccess($keys);
    }

    public function getTest(){
        echo "Cache: ".Cache::get(KEY_CACHE_PRIMARY_KEY_ENCRYPTION)."                             ";
        $date = Librarys_::getDate();
        $idService = "6775c0d57dbae11c5bb50fc29449e326ea772140dcae06c5b2b41624fe9f0965";
        $codeService = "bceaddcabae5fad99ed658696de89eb833d4719c45ad911c101fed7449e29aed";
        $keys_ = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJkYXRlIjoiMjAyMi0xMS0wOSIsImlkX3NlcnZpY2VfdXNpbmciOjQsImNvZGUiOiJiY2VhZGRjYWJhZTVmYWQ5OWVkNjU4Njk2ZGU4OWViODMzZDQ3MTljNDVhZDkxMWMxMDFmZWQ3NDQ5ZTI5YWVkIn0.janXCRVfc4DadW6v2RitQJOcymFWXotIId3VHt1RiyU";
        $keys = Encryptions_::encode([
            DATE => $date,
            AUTHENTICATION => $idService,
            FIELD_CODE_SERVICE => $codeService
        ],$keys_);
        return ResultRequest::exportResultSuccess($keys);
    }
}
