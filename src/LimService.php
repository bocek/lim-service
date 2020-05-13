<?php
namespace ServiceBox\LimService;


use App\Entity\Main\User;
use Symfony\Component\HttpClient\HttpClient;

/**
 * Class LimService
 * @package App\Service
 * @author  Cuneyt Ekinci
 * @copyright BK Mobil 2020
 */
class LimService {

    private $projectKey;
    private $username;
    private $password;
    private $client;
    private $base = $_ENV["LIM_SERVICE_ENDPOINT"];
    private $user = false;
    private $permissions = [];
    private $data = [];
    private $jwt = "";

    private $error;

    /**
     * LimService constructor.
     */
    public function __construct()
    {
        $this->projectKey = $_ENV["LIM_PROJECT_KEY"];
        $this->password = $_ENV["LIM_PASSWORD"];
        $this->username = $_ENV["LIM_USERNAME"];
        $this->client =  $client = HttpClient::create(['headers' => [
            'Content-Type' => 'application/json',
        ]]);
    }


    /**
     * @return array
     */
    public function getPermissions(): array {
        return $this->permissions;
    }


    /**
     * @param $permission (  linspire:admin,metodbox,pai:editor etc..  )
     * @return bool
     */
    public function hasPermission($permission): bool {

        return array_search($permission,$this->permissions)!==false;
    }

    /**
     * @param $key (height, weight, school etc )
     * @return bool
     */
    public function hasUserData($key): bool {
       return  array_search($key,$this->data)!==false;
    }

    /**
     * @param $key
     * @return mixed|null
     */
    public function getUserData($key){
        return $this->hasUserData($key) ? $this->data[$key] : null ;

    }


    /**
     * @return array|null
     */
    public function getUser(): ?array {
        return $this->user;
    }

    /**
     * @return mixed
     */
    public function getError(){
        return $this->error;
    }

    /**
     * @param $data
     * @return $this
     */
    public function setError($data): self {
        $this->error = $data;
        return $this;
    }


        /**
     * @param $username ( email or phone number )
     * @param $password
     * @return array
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function login($username,$password){
        $authResponse =  $this->client->request('POST', $this->base."/login", [
            'headers'=>[
                'Content-Type' => 'application/json',
              'projectKey'=>$this->projectKey // ask one from  LIM Admin
            ],
            'json' => [
                'username' => $username,
                'password'=>$password
            ],
        ])->toArray(false);



        if($authResponse && isset($authResponse["token"])){

            $this->user = $authResponse["user"];
            $this->permissions = $authResponse["permissions"];
            $this->data = $authResponse["data"];
            $this->jwt = $authResponse["token"];
            return true;
        }


        if($authResponse["code"] && $authResponse["code"]===401)
            $this->error = "Geçersiz kimlik bilgisi";
        else $this->error = $authResponse;
        return false;



    }


}
