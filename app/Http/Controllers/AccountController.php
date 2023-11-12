<?php

namespace App\Http\Controllers;

use App\Librarys\Encryptions_;
use App\Librarys\Librarys_;
use App\Librarys\ResultRequest;
use App\Models\Account;
use App\Models\Session;
use Illuminate\Http\Request;
use Psy\Readline\Hoa\Exception;

class AccountController extends Controller
{
    static public function add(Request $request)
    {
        $request->validate([
            FIELD_PASSWORD => REQUIRED,
            FIELD_USERNAME => REQUIRED
        ]);

        $username = $request->input(FIELD_USERNAME);
        $password = $request->input(FIELD_PASSWORD);

        $query = Account::where([
            FIELD_USERNAME => $username
        ])->get()->first();
        if ($query) {
            return ResultRequest::exportResultFailed(ACCOUNT_EXIST);
        }
        $query = Account::insert([
            FIELD_ID => null,
            FIELD_USERNAME => $username,
            FIELD_PASSWORD => base64_encode(Encryptions_::hash256($password))
        ]);
        if (!$query) {
            return ResultRequest::exportResultFailed(ADD_DATA_FAILED);
        }
        return ResultRequest::exportResultSuccess([
            MESSAGE => ADD_DATA_SUCCESS
        ]);
    }

    static public function login(Request $request)
    {
        $request->validate([
            FIELD_PASSWORD => REQUIRED,
            FIELD_USERNAME => REQUIRED
        ]);

        $username = $request->input(FIELD_USERNAME);
        $password = $request->input(FIELD_PASSWORD);

        $query = Account::where([
            FIELD_USERNAME => $username,
            FIELD_PASSWORD => base64_encode(Encryptions_::hash256($password))
        ])->get()->first();
        if (!$query) {
            return ResultRequest::exportResultFailed(LOGIN_FAILED);
        }
        $data = [
            FIELD_TIME_CREATE => Librarys_::getDateTime(),
            FIELD_USERNAME => $username
        ];
        $token = base64_encode(Encryptions_::hash256(json_encode($data)));

        $query = Session::insert([
            FIELD_ID => null,
            FIELD_ID_ACCOUNT => $query[FIELD_ID],
            FIELD_TOKEN => $token
        ]);
        if (!$query) {
            return ResultRequest::exportResultInternalServerError();
        }
        return ResultRequest::exportResultSuccess([
            ACCESS_TOKEN_COOKIE => $token
        ]);
    }

    static public function update(Request $request)
    {
        $request->validate([
            FIELD_PASSWORD => REQUIRED,
            FIELD_USERNAME => REQUIRED
        ]);

        $username = $request->input(FIELD_USERNAME);
        $password = $request->input(FIELD_PASSWORD);

        $query = Account::where([
            FIELD_USERNAME => $username
        ])->update([
            FIELD_PASSWORD => base64_encode(Encryptions_::hash256($password))
        ]);

        if (!$query) {
            return ResultRequest::exportResultInternalServerError();
        }
        return ResultRequest::exportResultSuccess([
            MESSAGE => UPDATE_DATA_SUCCESS
        ]);
    }

    static public function get()
    {

        $query = Account::get();
        if (!$query) {
            return ResultRequest::exportResultInternalServerError();
        }
        $data = [];
        foreach ($query as $item) {
            $data[] = [
                FIELD_ID => $item[FIELD_ID],
                FIELD_USERNAME => $item[FIELD_USERNAME],
            ];
        }
        return ResultRequest::exportResultSuccess([
            DATA => $data
        ]);
    }

    static public function logout(Request $request)
    {

        $token = $request->input(FIELD_TOKEN);
        $query = Session::where([
            FIELD_TOKEN => $token
        ])->delete();
        if (!$query) {
            return ResultRequest::exportResultFailed(LOGOUT_FAILED);
        }

        return ResultRequest::exportResultSuccess([
            MESSAGE => LOGOUT_SUCCESS
        ]);
    }
}
