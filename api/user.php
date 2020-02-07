<?php
//include the credentials file
require_once("creds.php");
require_once("database.php");
//include the JWT libraries
require_once 'jwt/BeforeValidException.php';
require_once 'jwt/ExpiredException.php';
require_once 'jwt/SignatureInvalidException.php';
require_once 'jwt/JWT.php';
use \Firebase\JWT\JWT;
//start the User class
class User
{
    private $id;
    private $email;
    private $firstName;
    private $lastName;
    private $permission;
    //getters and setters
    function setID($id)
    {
        $this->id = $id;
    }
    function setEmail($email)
    {
        $this->email = $email;
    }
    function setFirstname($firstName)
    {
        $this->firstName = $firstName;
    }
    function setLastname($lastName)
    {
        $this->lastName = $lastName;
    }
    function setPermission($perm)
    {
        $this->permission = $perm;
    }
    function getID()
    {
        return $this->id;
    }
    function getEmail()
    {
        return $this->email;
    }
    function getFirstname()
    {
        return $this->firstName;
    }
    function getLastname()
    {
        return $this->lastName;
    }
    function getPermission(){
        return $this->permission;
    }
    function generateAccessJWT()
    {
        $tokenId = base64_encode(random_bytes(32));
        $issuedAt = time();
        $notBefore = $issuedAt;
        $expire = $notBefore + 900;
        $payload = array(
            "iat" => $issuedAt,
            "jti" => $tokenId,
            "nbf" => $notBefore,
            "exp" => $expire,
            "firstName" => $this->firstName,
            "lastName" => $this->lastName,
            "permission" => $this->permission,
            "id" => $this->id
        );
        global $key;
        return (JWT::encode($payload, $key));
    }
    function generateRefreshJWT()
    {
        $tokenId = base64_encode(random_bytes(32));
        $issuedAt = time();
        $notBefore = $issuedAt;
        $expire = $notBefore + 604800; //1 week expiration period
        $payload = array(
            "iat" => $issuedAt,
            "jti" => $tokenId,
            "nbf" => $notBefore,
            "exp" => $expire,
            "firstName" => $this->firstName,
            "lastName" => $this->lastName,
            "permission" => $this->permission,
            "id" => $this->id
        );
        global $key;
        global $db;
        $jwt = JWT::encode($payload, $key);
        return (JWT::encode($payload, $key));
    }
}
