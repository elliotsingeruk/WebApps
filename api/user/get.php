<?php
include_once("../database.php");
include_once("../auth.php");
if (isset($_GET["id"])) {
    switch ($_GET["id"]) {
        case "all":
            return getAllUsers();
            break;
        default:
            return getUser($_GET["id"]);
            break;
    }
} else {
    die("Invalid Request");
}


function getUser($id){
    if(isset($_COOKIE["jwt"])){
    $jwt = checkJwt($_COOKIE["jwt"]);
    if($jwt != null){
        //only the user in question and admins can access user information 
        if($jwt > 2){
            global $db;
            $res = $db->query("SELECT id, firstName, lastName, email, permission_id FROM users INNER JOIN user_permission ON users.id = user_permission.user_id WHERE id = '$id'");
            $row = $res->fetch_assoc();
            if($res->num_rows == 1){
                $json = null;
                $json->id = $row["id"];
                $json->firstName = $row["firstName"];
                $json->lastName = $row["lastName"];
                $json->email = $row["email"];
                $json->permission = $row["permission_id"];
                echo(json_encode($json));
            } else if(decodeJwt($_COOKIE["jwt"]->id == $id)){
            global $db;
            $res = $db->query("SELECT id, firstName, lastName, email FROM users INNER JOIN user_permission ON users.id = user_permission.user_id WHERE id = '$id'");
            $row = $res->fetch_assoc();
            if($res->num_rows == 1){
                $json = null;
                $json->id = $row["id"];
                $json->firstName = $row["firstName"];
                $json->lastName = $row["lastName"];
                $json->email = $row["email"];
                echo(json_encode($json));
            }
        }
    }
    }
}
die("Invalid Token");
}
function getAllUsers()
{
}
