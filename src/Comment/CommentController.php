<?php


namespace Anax\Comment;

/**
 * To attach comments-flow to a page or some content.
 *
 */
class CommentController implements \Anax\DI\IInjectionAware
{
    use \Anax\DI\TInjectable;

    /**
     * Initialize the controller. Sets up the models for the
     * two tables in db.
     *
     * @return void
     */
    public function initialize()
    {
        $this->di->session();

        $this->comments1 = new \Anax\Comment\Comments1();
        $this->comments1->setDI($this->di);

        $this->comments2 = new \Anax\Comment\Comments2();
        $this->comments2->setDI($this->di);
    }

    /**
     * Sets up view to view all comments.
     *
     * Gets all comments for a specific page and sets up the view to view all
     * the comments.
     *
     * @param  string $key the array index for the stored comments in session.
     *
     * @return void.
     */
    public function viewAction($key = null)
    {
        $comments = $this->getModelForDb($key);
        $all = $comments->findAll();

        $this->views->add('comment/comments', [
            'comments'  => $all,
            'pageKey'   => $key,
        ], 'main-wide');
    }

    /**
     * Gets the path for the model object, which handles
     * the database table. The name of the table is the
     * name of the model.
     *
     * @param  string $key the model name / db table name
     *
     * @return model the model object for a specific table.
     */
    private function getModelForDb($key)
    {
        if (strcmp($key, "comments1") === 0) {
            return $this->comments1;
        } else {
            return $this->comments2;
        }
    }

    /**
     * Sets up the view to add new comments.
     *
     * Sets up the view for the form to be able to add new comments.
     *
     * @param  string $pageKey the index to store new comments in session.
     *
     * @return void.
     */
    public function viewAddAction($pageKey)
    {
        $form = new \Anax\HTMLForm\Comments\CFormAddComment($pageKey);
        $form->setDI($this->di);
        $status = $form->check();

        $this->di->theme->setTitle("Lägg till en ny kommentar");
        $this->setIndexPageTitle($pageKey);
        $this->di->views->add('comment/form', [
            'subtitle' => "Lägg till en ny kommentar",
            'form' => $form->getHTML(['legend' => 'Lämna en kommentar']),

           ], 'main-wide');
    }

    /**
     * Sets the main title for the comment page.
     *
     * Sets the main title (tag h1) for the comment page depending which page
     * it is.
     *
     * @param string $pageKey the page.
     *
     * @return void.
     */
    private function setIndexPageTitle($pageKey)
    {
        $titles = [
            'comments1' => "Anax-MVC kommentarsida 1",
            'comments2' => "Anax-MVC kommentarsida 2"
        ];

        $this->theme->setTitle($titles[$pageKey]);
        $this->views->add('comment/index', [
            'pageTitle' => $this->theme->getVariable("title")
        ], 'main-wide');
    }

    /**
     * Sets up the view for the edit form.
     *
     * Sets up the view for the form to make it possible to edit a specified
     * comment in the session.
     *
     * @param  string  $pageKey the index in session where the comments are stored.
     * @param  integer $id the id of the comment in the array for comments stored
     *                     in session.
     *
     * @return void.
     */
    public function viewEditAction($pageKey, $id)
    {
        $comments = $this->getModelForDb($pageKey);
        $comment = $comments->find($id);

        if ($comment) {
            $form = new \Anax\HTMLForm\Comments\CFormUpdateComment($comment->getProperties(), $pageKey);
            $form->setDI($this->di);
            $status = $form->check();

            $this->di->theme->setTitle("Ändra kommentar");
            $this->setIndexPageTitle($pageKey);
            $this->di->views->add('comment/form', [
                'subtitle' => "Ändra kommentar",
                'form' => $form->getHTML(['legend' => 'Ändra en kommentar']),
            ], 'main-wide');

        } else {
            $content = [
                'subtitle' => 'Hittar ej kommentar',
                'message' =>  'Hittar ej kommentar med id: ' . $id
            ];

            $this->showNoSuchCommentMessage($pageKey, $content);
        }
    }

    /**
     * Helper function for initiate no such comment view.
     *
     * Initiates a view which shows a message the comment with the specfic
     * id is not found. Contains a return button.
     *
     * @param  [] $content the subtitle and the message shown at page.
     *
     * @return void
     */
    private function showNoSuchCommentMessage($pageKey, $content)
    {
        $this->theme->setTitle("View user with id");
        $this->setIndexPageTitle($pageKey);
        $this->views->add('error/errorInfo', [
            'subtitle' => $content['subtitle'],
            'message' => $content['message'],
        ], 'main-wide');
    }

    /**
     * Sets up the view for the delete form.
     *
     * Sets up the view for the form to make it possible to delete a specified
     * comments in the session.
     *
     * @param  string  $pageKey the index in session where the comments are stored.
     * @param  integer $id the id of the comment in the array for comments stored
     *                     in session.
     *
     * @return void
     */
    public function viewDeleteAction($pageKey, $id)
    {
        $comments = $this->getModelForDb($pageKey);
        $comment = $comments->find($id);

        if ($comment) {
            $form = new \Anax\HTMLForm\Comments\CFormDeleteComment($comment->getProperties(), $pageKey);
            $form->setDI($this->di);
            $status = $form->check();

            $this->di->theme->setTitle("Ta bort kommentar");
            $this->setIndexPageTitle($pageKey);
            $this->di->views->add('comment/form', [
                'subtitle' => "Ta bort kommentar",
                'form' => $form->getHTML(['legend' => 'Ta bort kommentaren']),
            ], 'main-wide');
        } else {
            $content = [
                'subtitle' => 'Hittar ej kommentar',
                'message' =>  'Hittar ej kommentar med id: ' . $id
            ];

            $this->showNoSuchCommentMessage($pageKey, $content);
        }
    }
}
