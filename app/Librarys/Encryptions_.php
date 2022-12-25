<?php
namespace App\Librarys;

use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use UnexpectedValueException;

/**
 * @method static _handleJsonError(int $errno)
 */
class Encryptions_{

    // Hàm kiểm tra định dạng mật khẩu
    static public function checkFormatPassword($password){

        $trickger = "^([A-Z]){1}([\w_\.!@#$%&*()]+){5,31}$";
        if(preg_match($trickger ,trim($password), $matchs) && strlen(trim($password))>=8){
            return false;
        }else{
            return true;
        }
    }

    // Mã hóa password
    static public function encodePassword($password,$random,$time){
        $password_sha1 = sha1($password);
        $random_md5 = md5($random.$time);
        $len_password_sha1 = strlen($password_sha1);
        $marge_password = substr_replace($password_sha1,$random_md5,$len_password_sha1/2,0);
        return Encryptions_::encode(base64_encode(sha1($marge_password,TRUE)),$random);
    }

    // Chuyển username thành id số
    static public function convertUsernameToIDNumber($username){
        $md5_encode = md5($username);
        $result = "";
        for ($i=0;$i<strlen($md5_encode);$i++)
        {
            $result = $result.((int)$md5_encode[$i]);
        }
        return $result;
    }

    // Lấy password mã hóa
    static public function getEncodePassword($username,$password){
        $queryAccount = DB::table(TABLE_KEYS)->where([
            FIELD_USERNAME => $username
        ])->get();
        if($queryAccount){
            if($queryAccount->count()>0){
                $idAccount = $queryAccount->value(FIELD_ID);
                $codeEncryption = DB::table(TABLE_ENCRYPTION)->where(
                    [FIELD_ID_ACCOUNT => $idAccount]
                )->get()->value(FIELD_CODE);

                $encodePassword = Encryptions_::encodePassword($password,$codeEncryption,str_replace(" ","_",$queryAccount->value(FIELD_TIME_CREATE)));

                return $encodePassword;
            }else{
                return _NULL;
            }
        }else{
            return _NULL;
        }
    }

    public static function decodeV2($jwt, $key = null, $verify = true)
    {

        $tks = explode('.', $jwt);
        if (count($tks) != 3) {
            return 'Wrong number of segments';
        }
        [$headb64, $bodyb64, $cryptob64] = $tks;
        if (null === ($header = Encryptions_::jsonDecode(Encryptions_::urlsafeB64Decode($headb64)))) {
            return 'Invalid segment encoding';
        }
        if (null === $payload = Encryptions_::jsonDecode(Encryptions_::urlsafeB64Decode($bodyb64))) {
            return 'Invalid segment encoding';
        }

        $sig = Encryptions_::urlsafeB64Decode($cryptob64);
        if ($verify) {
            if (empty($header->alg)) {
                throw new DomainException('Empty algorithm');
            }
            print_r([
                "a" => $sig,
                "b" =>Encryptions_::sign($headb64.".".$bodyb64, $key, $header->alg),
                "c" => $headb64.".".$bodyb64,
                "d" => $key
            ]);
            echo "<br/><br/>";
            if ($sig != $key) {
               return SIG_VERI_FAILED;
            }
        }
        return $payload;
    }

    public static function decode($jwt, $key = null, $verify = true)
    {

        $tks = explode('.', $jwt);
        if (count($tks) != 3) {
            return 'Wrong number of segments';
        }
        [$headb64, $bodyb64, $cryptob64] = $tks;
        if (null === ($header = Encryptions_::jsonDecode(Encryptions_::urlsafeB64Decode($headb64)))) {
            return 'Invalid segment encoding';
        }
        if (null === $payload = Encryptions_::jsonDecode(Encryptions_::urlsafeB64Decode($bodyb64))) {
            return 'Invalid segment encoding';
        }

        $sig = Encryptions_::urlsafeB64Decode($cryptob64);
        if ($verify) {
            if (empty($header->alg)) {
                throw new DomainException('Empty algorithm');
            }
            if ($sig != Encryptions_::sign($headb64.".".$bodyb64, $key, $header->alg)) {
                return SIG_VERI_FAILED;
            }
        }
        return $payload;
    }

    public static function encode($payload, $key, $algo = 'HS256')
    {
        $header = array('typ' => 'JWT', 'alg' => $algo);
        $segments = array();
        $segments[] = Encryptions_::urlsafeB64Encode(Encryptions_::jsonEncode($header));
        $segments[] = Encryptions_::urlsafeB64Encode(Encryptions_::jsonEncode($payload));
        $signing_input = implode('.', $segments);
        $signature = Encryptions_::sign($signing_input, $key, $algo);
        $segments[] = Encryptions_::urlsafeB64Encode($signature);
        return implode('.', $segments);
    }

    public static function sign($msg, $key, $method = 'HS256')
    {
        $methods = array(
            'HS256' => 'sha256',
            'HS384' => 'sha384',
            'HS512' => 'sha512',
        );
        if (empty($methods[$method])) {
            throw new DomainException('Algorithm not supported');
        }
        return hash_hmac($methods[$method], $msg, $key, true);
    }


    public static function jsonDecode($input)
    {
        $obj = json_decode($input);
        if (function_exists('json_last_error') && $errno = json_last_error()) {
            Encryptions_::_handleJsonError($errno);
        } else if ($obj === null && $input !== 'null') {
            throw new DomainException('Null result with non-null input');
        }
        return $obj;
    }

    public static function jsonEncode($input)
    {
        $json = json_encode($input);
        if (function_exists('json_last_error') && $errno = json_last_error()) {
            Encryptions_::_handleJsonError($errno);
        } else if ($json === 'null' && $input !== null) {
            throw new DomainException('Null result with non-null input');
        }
        return $json;
    }

    public static function urlsafeB64Decode($input)
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }

    public static function urlsafeB64Encode($input)
    {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }

    /**
 * Encode data to Base64URL
 * @param string $data
 * @return boolean|string
 */
function base64url_encode($data)
{
  // First of all you should encode $data to Base64 string
  $b64 = base64_encode($data);

  // Make sure you get a valid result, otherwise, return FALSE, as the base64_encode() function do
  if ($b64 === false) {
    return false;
  }

  // Convert Base64 to Base64URL by replacing “+” with “-” and “/” with “_”
  $url = strtr($b64, '+/', '-_');

  // Remove padding character from the end of line and return the Base64URL result
  return rtrim($url, '=');
}

/**
 * Decode data from Base64URL
 * @param string $data
 * @param boolean $strict
 * @return boolean|string
 */
function base64url_decode($data, $strict = false)
{
  // Convert Base64URL to Base64 by replacing “-” with “+” and “_” with “/”
  $b64 = strtr($data, '-_', '+/');

  // Decode Base64 string and return the original data
  return base64_decode($b64, $strict);
}

}

?>
