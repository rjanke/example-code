<?php
/**
 * Alerts is a class that enables Session based alerts.
 *
 * Alerts can be added and then retrieved on a subsequent request
 * by the user to reveal messages of information, success, error etc. 
 * that occured during the processing of the previous request.
 * 
 * Example usage:
 * $alerts = new Alerts;
 * 
 * $alerts->create('test', [
 *     'message' => 'This is a test alert.', 
 *     'class' => 'success']);
 * 
 * $alerts->get('test');
 * 
 * @author Ryan Janke
 */
class Alerts
{
    /**
     * Creates an alert.
     * 
     * @param string $name The name of the alert that can be used
     *                     for lookup.
     * 
     * @param array $values Contains the alert message and CSS class
     *                      overrides.
     *
     * @return void
     */
    public function create($name, $values)
    {
        if (!isset($_SESSION['alert-message'])) 
        {
            $_SESSION['alert-message'] = array();
        }

        $_SESSION['alert-message'][$name] = $values;
    }

    /**
     * Gets a specific alert.
     * 
     * @param string $name The name of the alert that can be used
     *                     for lookup.
     *
     * @return array
     */
    public function get($name)
    {
        if (isset($_SESSION['alert-message'][$name]))
        {
            $alert = $_SESSION['alert-message'][$name];

            // Unsets a specific alert. 
            unset($_SESSION['alert-message'][$name]);
            return $alert;
        }
    }

    /**
     * Gets all the alerts.
     *
     * @return array
     */
    public function get_all()
    {
        if (isset($_SESSION['alert-message'])) 
        {
            $alerts = $_SESSION['alert-message'];

            // Unsets all alerts.
            unset($_SESSION['alert-message']);
            return $alerts;
        }
    }
}