<?php
require_once("../database.php");
require_once("../creds.php");

require_once '../jwt/BeforeValidException.php';
require_once '../jwt/ExpiredException.php';
require_once '../jwt/SignatureInvalidException.php';
require_once '../jwt/JWT.php';

use \Firebase\JWT\JWT;
