<?php
namespace App\Components\Passive;

class Encryption
{
  private $method = '';
  private $password_key = '';
  
  public function __construct()
  {
    $this->password_key = config('services.encryption.key');
    $this->method = config('services.encryption.method');
  }
  private function _encrypt($data){
    $value = openssl_encrypt($data,$this->method,$this->password_key);
    return $value;
  }

  private function _decrypt($data){
    try {
      $value = openssl_decrypt($data,$this->method,$this->password_key);
    } catch (\Exception $e) {
      $value = $data;
    }
    return $value;
  }

  public static function encrypt($data)
  {
    $encryption = new Encryption();
    return $encryption->_encrypt($data);
  }

  public static function decrypt($data)
  {
    $encryption = new Encryption();
    return $encryption->_decrypt($data);
  }


  public static function decryptCollections($collections,$encryptables)
  {
    $encryption = new Encryption();
    $data = [];


    foreach ($collections as $details) {
      $info = [];
      foreach ($details as $key => $value) {
        if (in_array($key, $encryptables)) {
          $info[$key] = $encryption->_decrypt($details[$key]);
        }else{
          $info[$key] = $value;
        }
      }
      $data[] = $info;
    }
    return $data;
  }

  public static function decryptData($encryptedData,$encryptables)
  {
    $data = new \stdClass();
    $encryption = new Encryption();
    $collections =  collect($encryptedData)->toArray();

    foreach ($collections as $key => $value) {
      if (in_array($key, $encryptables)) {
          $data->{$key} = $encryption->_decrypt($value);
      }else{
          $data->{$key} = $value;
      }
    }
    return $data;
  }

  public static function decryptMetaGroups($encryptedData)
  {

    // This will create decrypted_value as meta_value automatically encrypted and unchangable
    if($encryptedData) {
      foreach ($encryptedData as $field_group => &$group) {
          foreach ($group as $sub_filed_group => &$metas){
            foreach($metas as $key => &$meta){
                $meta->decrypted_value = $meta->meta_value;
            }
          }
      }
    }

    return $encryptedData;
  }
}