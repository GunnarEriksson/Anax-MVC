<?php

namespace Anax\HTMLForm\Comments;

/**
 * Anax base class for wrapping sessions.
 *
 */
class CFormDeleteComment extends \Mos\HTMLForm\CForm
{
    use \Anax\DI\TInjectionAware,
        \Anax\MVC\TRedirectHelpers;

    private $id;
    private $pageKey;



    /**
     * Constructor
     *
     * @param [] $userData the data connected to the user.
     * @param string $pageKey the page name for the comment.
     */
    public function __construct($userData, $pageKey)
    {
        $this->pageKey = $pageKey;
        $this->id = $userData['id'];

        parent::__construct([], [
            'content' => [
                'type'        => 'textarea',
                'label'       => 'Kommentar',
                'required'    => true,
                'validation'  => ['not_empty'],
                'value'       => $userData['content'],
                'readonly'    => true,
            ],
            'name' => [
                'type'        => 'text',
                'label'       => 'Namn',
                'required'    => true,
                'validation'  => ['not_empty'],
                'value'       => $userData['name'],
                'readonly'    => true,
            ],
            'web' => [
                'type'        => 'text',
                'label'       => 'Hemsida',
                'required'    => false,
                'validation'  => ['not_empty'],
                'value'       => $userData['web'],
                'readonly'    => true,
            ],
            'mail' => [
                'type'        => 'text',
                'label'       => 'E-post',
                'required'    => true,
                'validation'  => ['not_empty', 'email_adress'],
                'value'       => $userData['mail'],
                'readonly'    => true,
            ],
            'submit' => [
                'type'      => 'submit',
                'callback'  => [$this, 'callbackSubmit'],
                'value'     => 'Radera',
            ],
            'submit-delete-all' => [
                'type'      => 'submit',
                'callback'  => [$this, 'callbackSubmitDeleteAll'],
                'value'     => 'Radera Allt',
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
     * @return boolean true if data was deleted in db, false otherwise.
     */
    public function callbackSubmit()
    {
        $this->deleteComment = $this->getModelObject($this->pageKey);
        $isSaved = $this->deleteComment->delete($this->id);

        return $isSaved;
    }



    /**
     * Callback for submit delete all button.
     *
     * @return boolean true if data was all data was deleted in db, false otherwise.
     */
    public function callbackSubmitDeleteAll()
    {
        $this->deleteComment = $this->getModelObject($this->pageKey);
        $isSaved = $this->deleteComment->deleteAll();

        return $isSaved;
    }



    /**
     * Gets the model object for the table in the database.
     *
     * @param  string $tableName the name of the table.
     *
     * @return object the model object for the table in database.
     */
    private function getModelObject($tableName)
    {
        if (strcmp($tableName, "comments1") === 0) {
            $model = new \Anax\Comment\Comments1();
        } else {
            $model = new \Anax\Comment\Comments2();
        }

        $model->setDI($this->di);

        return $model;
    }



    /**
     * Callback What to do if the form was submitted?
     *
     */
    public function callbackSuccess()
    {
        $this->redirectTo($this->pageKey);
    }



    /**
     * Callback What to do when form could not be processed?
     *
     */
    public function callbackFail()
    {
        $this->AddOutput("<p><i>Kommentarerna / kommentaren kunde inte raderas i databasen!</i></p>");
        $this->redirectTo();
    }
}
