<?php
/**
 * FormToken is a class that mangages CSRF form tokens.
 * 
 * FormToken can create, get, validate, and destroy unique tokens 
 * generated for specific HTML forms to help prevent CSRF attacks.
 * These tokens are saved in Session and added as a hidden field
 * on HTML forms. On a POST request, these two values can be 
 * compared to ensure request validity. 
 * 
 * Example usage:
 * $token = new FormToken;
 * 
 * Create token on initial request.
 * $token->create('save_profile');
 * 
 * Get this token for display in the HTML form.
 * $token->get('save_profile');
 * 
 * Validate on POST request.
 * $token->validate('save_profile');
 * 
 * Remove existing token from Session.
 * $token->destroy('save_profile');
 *
 * @author Ryan Janke 
 */
class FormToken 
{
    /**
     * Creates a new, random, token.
     *
     * @param string $form_name The name of the form token is created for.
     *
     * @return void
     */
    public function create($form_name)
    {
        if (!isset($_SESSION[$form_name . '-token'])) 
        {
            $_SESSION[$form_name . '-token'] = bin2hex(random_bytes(32));
        }
    }

    /**
     * Gets an existing token.
     *
     * @param string $form_name The name of the form that can be used
     *                          for token lookup.
     *
     * @return array
     */
    public function get($form_name)
    {
        if (isset($_SESSION[$form_name . '-token']))
        {
            return array(
                'name' => $form_name . '-token',
                'token' => $_SESSION[$form_name . '-token']
            );
        }
    }

    /**
     * Validates form and Session tokens.
     *
     * @param string $form_name The name of the form that can be used
     *                          for token lookup.
     *
     * @return bool
     */
    public function validate($form_name)
    {
        if (isset($_POST[$form_name . '-token'])) {
            if (hash_equals($_SESSION[$form_name . '-token'], $_POST[$form_name . '-token'])) 
            {
                // Tokens match.
                return true;
            } 
            else 
            {
                // Tokens do not match.
                return false;
            }
        }
        else 
        {
            // POST request lacks token field.
            return false;
        }
    }

    /**
     * Destroys an existing token.
     *
     * @param string $form_name The name of the form that can be used
     *                          for token lookup.
     *
     * @return void
     */
    public function destroy($form_name)
    {
        if (isset($_SESSION[$form_name . '-token'])) 
        {
            unset($_SESSION[$form_name . '-token']);
        }
    }
}