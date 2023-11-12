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

    public static function hash256($text): string
    {
        return hash("sha256",$text);
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
}

?>
