<?php
/**
 * An example of a JSON endpoint that uses 
 * JSON Web Tokens (JWTs) as an authentication 
 * method, uses a SQL connection to query data, 
 * and then returns a barcode image encoded in Base64 in a
 * JSON response.
 * 
 * Verbose error checking ommitted for program clarity.
 * 
 * @author Ryan Janke
 */

// Load 3rd party Composer libs.
require 'vendor/autoload.php';
use Firebase\JWT\JWT;
$generator = new Picqer\Barcode\BarcodeGeneratorPNG();

//////////////////////////
// CONFIG
//////////////////////////

// Set up PDO database connection.
$dbname = "example_db";
$db_username = "example_username";
$db_password = "example_password";
$host = "db.example.com";

try
{
    $db = new PDO('mysql:host=' . $host . ';dbname=' . $dbname,
                    $db_username, 
                    $db_password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch(PDOException $e)
{
    $error = $e->getMessage();
    // Additional DB error logging here.
}

// JWT shared secret.
$shared_secret = 'asupersecrettopsecretsuperlongstringthatisnottobeshared';

//////////////////////////
// CONFIG
//////////////////////////


//////////////////////////
// REQUEST AND JWT VERIFICATION
//////////////////////////

// Ensure Bearer Authorization token is set in HTTP header.
if (! preg_match('/Bearer\s(\S+)/', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
    header('HTTP/1.0 400 Bad Request');
    echo 'Token not found in request';
    exit;
}

// Check for first captured parenthesized subpattern (the token itself).
$jwt = $matches[1];
if (! $jwt) {
    header('HTTP/1.0 400 Bad Request');
    exit;
}

// Decode the JWT
$decoded = JWT::decode($jwt, $shared_secret, array('HS256'));

// Person ID comes from 'sub' field in JWT.
$person_id = $decoded->sub;

//////////////////////////
// REQUEST AND JWT VERIFICATION
//////////////////////////


//////////////////////////
// QUERY DB AND CREATE JSON RESPONSE
//////////////////////////

// Query the database using the value from the JWT.
$sql = $db->prepare('SELECT 
                            barcode 
                        FROM 
                            PEOPLE 
                        WHERE 
                            person_id = :person_id 
                        LIMIT 1');
$sql->bindParam(':person_id', $person_id);
$sql->execute();

$result = $sql->fetch(PDO::FETCH_ASSOC);

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
// SQL query returned nothing.
else 
{
    header('HTTP/1.0 400 Bad Request');
    exit;
}

//////////////////////////
// QUERY DB AND CREATE JSON RESPONSE
//////////////////////////