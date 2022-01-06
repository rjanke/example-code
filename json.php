<?php
/**
 * An example of a JSON endpoint that uses JWTs
 * as an authentication method, uses a SQL 
 * connection to query data, and then returns
 * a barcode image encoded in Base64 in a
 * JSON response.
 * 
 * 
 * @author Ryan Janke
 */

// Load required JWT and barcode generator libraries.
require_once('../vendor/autoload.php');
use Firebase\JWT\JWT;
$generator = new Picqer\Barcode\BarcodeGeneratorPNG();

//////////////////////////
// CONFIG
//////////////////////////
//
//
// Set up database connection.
$dbname = "example_db";
$db_username = "example_user";
$db_password = "example_pass";
$host = "db.example.com";

try
{
    $dbcon = new PDO('mysql:host=' . $host . ';dbname=' . $dbname,
                    $db_username, 
                    $db_password);
    $dbcon->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch(PDOException $e)
{
    $error = 'ERROR: ' . $e->getMessage();
    // Log errors.
}

// Report all errors. Development only.
ini_set("display_errors", 1);
error_reporting(E_ALL);

// JWT shared secret. Application that interacts with this API knows this secret.
$shared_secret = 'asupersecrettopsecretsuperlongstringthatisnottobeshared';
//
//
//////////////////////////
// CONFIG
//////////////////////////


//////////////////////////
// REQUEST AND JWT VERIFICATION
//////////////////////////
//
//
// Make sure Bearer Authorization token is set in HTTP header.
if (! preg_match('/Bearer\s(\S+)/', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
    header('HTTP/1.0 400 Bad Request');
    echo 'Token not found in request';
    exit;
}

// Save JWT or return 400 status.
$jwt = $matches[1];
if (! $jwt) {
    // Token was able to be extracted from the authorization header
    header('HTTP/1.0 400 Bad Request');
    exit;
}

// Decode the JWT
$decoded = JWT::decode($jwt, $shared_secret, array('HS256'));

// Person ID comes from 'sub' field in JWT.
$person_id = $decoded->sub;
//
//
//////////////////////////
// REQUEST AND JWT VERIFICATION
//////////////////////////


// Query the database using the value from the JWT.
$sql = $dbcon->prepare('SELECT 
                            barcode 
                        FROM 
                            PEOPLE 
                        WHERE 
                            person_id = :person_id 
                        LIMIT 1');
$sql->bindParam(':person_id', $person_id);
$sql->execute();

$result = $sql->fetch(PDO::FETCH_ASSOC);

// If query returns a result.
if ($result)
{
    // Get barcode number from query.
    $barcode = $result['barcode'];

    // Generate the image of the barcode, from the barcode number, using a specific barcode format. 
    $barcode_image_base64 = base64_encode($generator->getBarcode($barcode, $generator::TYPE_CODE_128, 2, 75,));

    // Assemble the data we want to return. In this case, some HTML via JSON.
    $html = "<div>
        <p>Hello, authenticated API User.</p>
        <p>Your Person ID: <strong>" . $person_id . "</strong></p>
        <p>The barcode value is: <strong>" . $barcode . "</strong></p>
        <img src='data:image/png;base64," . $barcode_image_base64 . "' alt='barcode'/>
    </div>";

    // Assemble final array structure.
    $data = array(
        "metadata" => array(
                "version" => "1"),
        "content" => array(
            array(
                "elementType" => "html",
                "html" => $html
            )
        ));

    // Encode the array as JSON, set correct HTTP header, and display.
    $json = json_encode($data);
    header('Content-type:application/json');
    echo($json);
    exit();
}
else 
{
    // SQL query returned nothing.
    header('HTTP/1.0 400 Bad Request');
    exit;
}