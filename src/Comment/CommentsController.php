<?php

namespace Anax\Comment;

/**
 * To attach comments-flow to a page or some content.
 *
 */
class CommentsController implements \Anax\DI\IInjectionAware
{
    use \Anax\DI\TInjectable;
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
        $comments = new \Anax\Comment\CommentsInSession();
        $comments->setDI($this->di);
        $all = $comments->findAll($key);
        $this->views->add('comment/comments', [
            'comments'  => $all,
            'pageKey'   => $key,
        ]);
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
        $comments = new \Anax\Comment\CommentsInSession();
        $comments->setDI($this->di);
        $formValues = $this->setFormValues($pageKey);
        $this->theme->addStylesheet('css/form.css');
        $this->setIndexPageTitle($pageKey);
        $this->views->add('comment/form', $formValues);
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
        ]);
    }
    /**
     * Helper function to set the form values.
     *
     * Sets the values in the form fields if they are present.
     *
     * @param  string $pageKey  the index in session where comments are stored.
     * @param  string $comment  the comment string for the comment field.
     * @param  string $output   the messages, which can be shown in the form.
     * @param  int $id          the id for the comment. Not valid for adding comments.
     *
     * @return []   the array of form fields values.
     */
    private function setFormValues($pageKey = null, $comment = null, $output = null, $id = null)
    {
        $mail = isset($comment['mail']) ? $comment['mail'] : null;
        $web = isset($comment['web']) ? $comment['web'] : null;
        $name = isset($comment['name']) ? $comment['name'] : null;
        $content = isset($comment['content']) ? $comment['content'] : null;
        $formValues = [
            'mail'      => $mail,
            'web'       => $web,
            'name'      => $name,
            'content'   => $content,
            'output'    => $output,
            'id'        => $id,
            'pageKey'   => $pageKey
        ];
        return $formValues;
    }
    /**
     * Add a comment.
     *
     * Adds the value in the form to the comments, which are stored in the session.
     * Redirects to the location, which are stated in the hidden input field
     * in the form.
     *
     * @return void
     */
    public function addAction()
    {
        $isPosted = $this->request->getPost('doCreate');
        if (!$isPosted) {
            $this->response->redirect($this->request->getPost('redirect'));
        }
        $comment = $this->getCommentSessionValuesFromForm();
        $comments = new \Anax\Comment\CommentsInSession();
        $comments->setDI($this->di);
        $comments->add($comment);
        $this->response->redirect($this->request->getPost('redirect'));
    }
    /**
     * Helper function to get the comment values from the form.
     *
     * Gets the value from the form fields sent as a POST.
     *
     * @return [] the array with the values from the form fields.
     */
    private function getCommentSessionValuesFromForm()
    {
        $comment = [
            'content'   => $this->request->getPost('content'),
            'name'      => $this->request->getPost('name'),
            'web'       => $this->request->getPost('web'),
            'mail'      => $this->request->getPost('mail'),
            'timestamp' => time(),
            'ip'        => $this->request->getServer('REMOTE_ADDR'),
            'id'        => $this->request->getPost('id'),
            'gravatar'  => 'http://www.gravatar.com/avatar/' . md5(strtolower(trim($this->request->getPost('mail')))) . '.jpg',
            'pageKey'   => $this->request->getPost('pageKey'),
        ];
        return $comment;
    }
    /**
     * Remove all comments.
     *
     * Deletes all comments, which are stored in session. Redirects to the
     * location, which are stated in the hidden input field in the form.
     *
     * @return void
     */
    public function removeAllAction()
    {
        $isPosted = $this->request->getPost('doRemoveAll');
        if (!$isPosted) {
            $this->response->redirect($this->request->getPost('redirect'));
        }
        $comments = new \Anax\Comment\CommentsInSession();
        $comments->setDI($this->di);
        $comments->deleteAll($this->request->getPost('pageKey'));
        $this->response->redirect($this->request->getPost('redirect'));
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
        $comments = new \Anax\Comment\CommentsInSession();
        $comments->setDI($this->di);
        $comment = $comments->findCommentById($pageKey, $id);
        $output = null;
        if (empty($comment)) {
            $output = "Kunde inte finna kommentar med id $id";
            $id = null;
        }
        $formValues = $this->setFormValues($pageKey, $comment, $output, $id);
        $this->theme->addStylesheet('css/form.css');
        $this->setIndexPageTitle($pageKey);
        $this->views->add('comment/editForm', $formValues);
    }
    /**
     * Edit a comment.
     *
     * Edits the comment in the session with the values from the form.
     * Redirects to the location, which are stated in the hidden input field
     * in the form.
     *
     * @return void
     */
    public function editAction()
    {
        $isPosted = $this->request->getPost('doEdit');
        if (!$isPosted) {
            $this->response->redirect($this->request->getPost('redirect'));
        }
        $comment = $this->getCommentSessionValuesFromForm();
        $comments = new \Anax\Comment\CommentsInSession();
        $comments->setDI($this->di);
        $comments->edit($comment);
        $this->response->redirect($this->request->getPost('redirect'));
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
        $comments = new \Anax\Comment\CommentsInSession();
        $comments->setDI($this->di);
        $comment = $comments->findCommentById($pageKey, $id);
        $output = null;
        if (empty($comment)) {
            $output = "Kunde inte finna kommentar med id $id";
            $id = null;
        }
        $formValues = $this->setFormValues($pageKey, $comment, $output, $id);
        $this->theme->addStylesheet('css/form.css');
        $this->setIndexPageTitle($pageKey);
        $this->views->add('comment/deleteForm', $formValues);
    }
    /**
     * Deletes a comment.
     *
     * Deletes the comment in the session.
     * Redirects to the location, which are stated in the hidden input field
     * in the form.
     *
     * @return void
     */
    public function deleteAction()
    {
        $isPosted = $this->request->getPost('doDelete');
        if (!$isPosted) {
            $this->response->redirect($this->request->getPost('redirect'));
        }
        $comment = [
            'id'        => $this->request->getPost('id'),
            'pageKey'   => $this->request->getPost('pageKey')
        ];
        $comments = new \Anax\Comment\CommentsInSession();
        $comments->setDI($this->di);
        $comments->delete($comment);
        $this->response->redirect($this->request->getPost('redirect'));
    }
}
