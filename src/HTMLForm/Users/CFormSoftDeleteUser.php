<?php

namespace Anax\HTMLForm\Users;

/**
 * Anax base class for wrapping sessions.
 *
 */
class CFormSoftDeleteUser extends \Mos\HTMLForm\CForm
{
    use \Anax\DI\TInjectionAware,
        \Anax\MVC\TRedirectHelpers;

    private $id;
    private $password;
    private $created;

    /**
     * Constructor
     *
     * @param [] the user data for the user.
     */
    public function __construct($userData)
    {
        $this->id = $userData['id'];
        $this->password = $userData['password'];
        $this->created = $userData['created'];

        parent::__construct([], [
            'acronym' => [
                'type'        => 'text',
                'label'       => 'Akronym',
                'required'    => false,
                'validation'  => ['not_empty'],
                'value'       => $userData['acronym'],
                'readonly'    => true,
            ],
            'name' => [
                'type'        => 'text',
                'label'       => 'Namn',
                'required'    => false,
                'validation'  => ['not_empty'],
                'value'       => $userData['name'],
                'readonly'    => true,
            ],
            'email' => [
                'type'        => 'text',
                'label'       => 'E-post',
                'required'    => false,
                'validation'  => ['not_empty', 'email_adress'],
                'value'       => $userData['email'],
                'readonly'    => true,
            ],
            'submit' => [
                'type'      => 'submit',
                'callback'  => [$this, 'callbackSubmit'],
                'value'     => 'Papperskorgen',
            ],
        ]);
    }



    /**
     * Customise the check() method.
     *
     * @param callable $callIfSuccess handler to call if function returns true.
     * @param callable $callIfFail    handler to call if function returns true.
     */
    public function check($callIfSuccess = null, $callIfFail = null)
    {
        return parent::check([$this, 'callbackSuccess'], [$this, 'callbackFail']);
    }



    /**
     * Callback for submit-button.
     *
     * @return boolean true if data was added in db, false otherwise.
     */
    public function callbackSubmit()
    {
        $now = gmdate('Y-m-d H:i:s');

        $this->removeUser = new \Anax\Users\User();
        $this->removeUser->setDI($this->di);
        $isSaved = $this->removeUser->save(array(
            'id'        => $this->id,
            'acronym'   => $this->Value('acronym'),
            'email'     => $this->Value('email'),
            'name'      => $this->Value('name'),
            'password'  => $this->password,
            'created'   => $this->created,
            'updated'   => $now,
            'deleted'   => $now,
            'active'    => null
        ));

        return $isSaved;
    }



    /**
     * Callback What to do if the form was submitted?
     *
     */
    public function callbackSuccess()
    {
        $this->redirectTo('users');
    }



    /**
     * Callback What to do when form could not be processed?
     *
     */
    public function callbackFail()
    {
        $this->AddOutput("<p><i>Användaren kunde inte läggas i papperskorgen!</i></p>");
        $this->redirectTo();
    }
}
