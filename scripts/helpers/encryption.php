<?php
class Encryption {
    private static $OPENSSL_CIPHER_NAME = "aes-128-cbc"; //Name of OpenSSL Cipher
    private static $CIPHER_KEY_LEN = 16; //128 bits
    /**
     * Encrypt data using AES Cipher (CBC) with 128 bit key
     *
     * @param type $key - key to use should be 16 bytes long (128 bits)
     * @param type $iv - initialization vector
     * @param type $data - data to encrypt
     * @return encrypted data in base64 encoding with iv attached at end after a :
     */
    static function encrypt($key, $iv, $data) {
        if (strlen($key) < Encryption::$CIPHER_KEY_LEN) {
            $key = str_pad("$key", Encryption::$CIPHER_KEY_LEN, "0"); //0 pad to len 16
        } else if (strlen($key) > Encryption::$CIPHER_KEY_LEN) {
            $key = substr($str, 0, Encryption::$CIPHER_KEY_LEN); //truncate to 16 bytes
        }

        $encodedEncryptedData = base64_encode(openssl_encrypt($data, Encryption::$OPENSSL_CIPHER_NAME, $key, OPENSSL_RAW_DATA, $iv));
        $encryptedPayload = $encodedEncryptedData;

        return $encryptedPayload;
    }
    /**
     * Decrypt data using AES Cipher (CBC) with 128 bit key
     *
     * @param type $key - key to use should be 16 bytes long (128 bits)
     * @param type $data - data to be decrypted in base64 encoding with iv attached at the end after a :
     * @return decrypted data
     */
    static function decrypt($key, $iv, $data) {
        if (strlen($key) < Encryption::$CIPHER_KEY_LEN) {
            $key = str_pad("$key", Encryption::$CIPHER_KEY_LEN, "0"); //0 pad to len 16
        } else if (strlen($key) > Encryption::$CIPHER_KEY_LEN) {
            $key = substr($str, 0, Encryption::$CIPHER_KEY_LEN); //truncate to 16 bytes
        }

        $decryptedData = openssl_decrypt(base64_decode($data), Encryption::$OPENSSL_CIPHER_NAME, $key, OPENSSL_RAW_DATA, $iv);

        return $decryptedData;
    }

    static function randomString($length) {
		$str = "";
		$characters = array_merge(range('a','z'), range('0','9'));
		$max = count($characters) - 1;
		for ($i = 0; $i < $length; $i++) {
			$rand = mt_rand(0, $max);
			$str .= $characters[$rand];
		}
		return $str;
	}
}
?>
