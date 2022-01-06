<?php
/**
 * A few examples of functions I have written in the past.
 * 
 * @author Ryan Janke
 */

///////////////////////////////
// SESSION BASED ALERTS
///////////////////////////////
//
//
/**
 * Adds an alert to the $_SESSION['alert-message'] array.
 * 
 * Alerts can be added and then retrieved on a subsequent request
 * by the user to reveal messages of information, success, error etc. 
 * that occured during the processing of the previous request.
 *
 * Usage:
 * Add an alert named 'test' by using:
 * 
 * $alert = array(
 *      'message' => 'this is the message',
 *      'class' => 'danger'
 * );
 * add_alert('test', $alert);
 *
 * @param string $name The name of the alert that can be used
 *                     for lookup.
 * 
 * @param array $values Contains the alert message and CSS class
 *                      overrides.
 *
 * @return void
 */
function add_alert($name, $values)
{
    // Ensure 'alert-message' array is set in the session.
    if (!isset($_SESSION['alert-message'])) 
    {
        $_SESSION['alert-message'] = array();
    }

    // Save the alert to the 'alert-message' array.
    $_SESSION['alert-message'][$name] = $values;
}


/**
 * Gets all alerts from the $_SESSION['alert-message'] array.
 *
 * Usage:
 * 
 * $alerts = get_alerts();
 *
 * @return array
 */
function get_alerts() 
{
    if (isset($_SESSION['alert-message'])) 
    {
        $alerts = $_SESSION['alert-message'];
        // Unset the whole 'alert-message' session array.
        unset($_SESSION['alert-message']);
        return $alerts;
    }
}

/**
 * Gets a single alert, by name, from the $_SESSION['alert-message'] array.
 *
 * Usage:
 * Get an alert named 'test' by using:
 * 
 * $alert = get_alert('test');
 * 
 * @param string $name The name of the alert that can be used
 *                     for lookup.
 *
 * @return array
 */
function get_alert($name)
{
    if (isset($_SESSION['alert-message'][$name]))
    {
        $alert = $_SESSION['alert-message'][$name];
        // Unset a specific alert in the 'alert-message' session array.
        unset($_SESSION['alert-message'][$name]);
        return $alert;
    }
}
//
//
///////////////////////////////
// SESSION BASED ALERTS
///////////////////////////////


///////////////////////////////
// CSRF TOKEN FORM SUBMISSIONS
///////////////////////////////
//
//
/**
 * Generates a unique session based form token to help prevent CSRF attacks.
 *
 * A random string of characters is saved in the session and 
 * used in a hidden input field on an HTML form. When a POST request is
 * made, the value of the hidden input is evaluated against the value 
 * saved in session.
 * 
 * @param string $formname The name of the form that can be used
 *                         for lookup.
 *
 * @return void
 */
function generate_form_token($formname)
{
    if (!isset($_SESSION[$formname . '-token'])) 
    {
        $_SESSION[$formname . '-token'] = bin2hex(random_bytes(32));
    }
}

/**
 * Gets a specific form token from the session.
 *
 * The values returned can then be passed to the template for rendering.
 * 
 * @param string $formname The name of the form that can be used
 *                         for lookup.
 *
 * @return array
 */
function get_form_token($formname)
{
    if (isset($_SESSION[$formname . '-token']))
    {
        return array(
            'name' => $formname . '-token',
            'token' => $_SESSION[$formname . '-token']
        );
    }
}

/**
 * Validates a form token against the session token.
 *
 * Once a form is submitted, the POST data must include the hidden token
 * field. This value is then compared against the value that is already 
 * saved in the session. If they match, then proceed, if not, then
 * reject the POST request. 
 * 
 * @param string $formname The name of the form that can be used
 *                         for lookup.
 *
 * @return bool
 */
function validate_form_token($formname)
{
    if (isset($_POST[$formname . '-token'])) {
        if (hash_equals($_SESSION[$formname . '-token'], $_POST[$formname . '-token'])) 
        {
            // Tokens match! Proceed.
            return true;
        } 
        else 
        {
            // Tokens do not match! 
            return false;
        }
    }
    else 
    {
        // POST request lacks token field!
        return false;
    }
}

/**
 * Unsets a form token from the session.
 *
 * A form token can be removed from the session once no 
 * longer needed (that form is no longer being displayed).
 * 
 * @param string $formname The name of the form that can be used
 *                         for lookup.
 *
 * @return void
 */
function unset_form_token($formname)
{
    if (isset($_SESSION[$formname . '-token'])) 
    {
        unset($_SESSION[$formname . '-token']);
    }
}
//
//
///////////////////////////////
// CSRF TOKEN FORM SUBMISSIONS
///////////////////////////////


///////////////////////////////
// GET RANDOM IMAGE 
///////////////////////////////
//
//
/**
 * Generates a random relative URL to an image.
 *
 * A relative URL to an image can be generated by providing a
 * path, image naming pattern, assumes the images are JPEG,
 * and named consistently. Random number is generated based on
 * how many files are in the image directory. 
 * 
 * Image name examples:
 * background-1.jpg
 * background-2.jpg
 * background-3.jpg
 * 
 * Usage:
 * get_random_image('/assets/images/backgrounds/', 'background-');
 * 
 * @param string $image_dir The relative path to the folder containing
 *                          the images.
 * 
 * @param string $image_pattern The name of image except for the number.
 *                         
 *
 * @return string
 */
function get_random_image($image_dir, $image_pattern) 
{
    // Count files in the image directory.
    $file_count = 0;
    $files = glob($_SERVER["DOCUMENT_ROOT"] . $image_dir . $image_pattern . '*');
    if ($files)
    {
        $file_count = count($files);
    }
    
    // Generate a random number.
    $random_number = rand(1, $file_count);

    // Assemble the final image path.
    $random_image_path = $image_dir . $image_pattern . $random_number . '.jpg';
    return $random_image_path;
}
///////////////////////////////
// GET RANDOM IMAGE
///////////////////////////////