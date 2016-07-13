<?php

namespace Anax\Comment;

/**
 * To attach comments-flow to a page or some content.
 *
 */
class CommentsInSession implements \Anax\DI\IInjectionAware
{
    use \Anax\DI\TInjectable;



    /**
     * Add a new comment.
     *
     * @param array $comment with all details.
     *
     * @return void
     */
    public function add($comment)
    {
        $comments = $this->session->get('comments', []);
        $pageKey = $comment['pageKey'];
        $comments["$pageKey"][] = $comment;
        $this->session->set('comments', $comments);
    }



    /**
     * Find and return all comments.
     *
     * Finds and returns all comments for a specific comments index in the
     * session.
     *
     * @return array with all comments for a specific index.
     */
    public function findAll($pageKey)
    {
        $comments = $this->session->get('comments', []);
        if (array_key_exists($pageKey, $comments)) {
            $comments = $comments["$pageKey"];
        } else {
            $comments = [];
        }

        return $comments;
    }



    /**
     * Delete all comments.
     *
     * Deletes all comments for a specific comments index in the session.
     *
     * @return void
     */
    public function deleteAll($pageKey)
    {
        $comments = $this->session->get('comments', []);
        if (array_key_exists($pageKey, $comments)) {
            $comments[$pageKey] = [];
            $this->session->set('comments', $comments);
        }
    }

    /**
     * Find comment by id.
     *
     * Checks if the specific index exist in the comments array in the session.
     * If it exists, it checks if a comment with the specified id exists. If
     * it does, the content of the comment is returned. If not, an empty array
     * is returned.
     *
     * @param  string $pageKey  the index in the comment array where the comments
     *                          are stored.
     * @param  integer $id      the content id.
     *
     * @return []   returns the content if id exists. If not, an empty array
     *              is returned.
     */
    public function findCommentById($pageKey, $id)
    {
        $comment = [];
        $comments = $this->session->get('comments', []);
        if (array_key_exists($pageKey, $comments)) {
            $comments = $comments["$pageKey"];
            if ($this->doesCommentIdExists($id, $comments)) {
                $comment = $comments[$id];
            }
        }

        return $comment;
    }

    /**
     * Helper function to check if comment id exists in an array of comments.
     *
     * Checks in an array of comments if the comment id exists.
     *
     * @param  integer $id the comment id.
     * @param  [] the array of comments.
     *
     * @return boolean true if comment id exists in session, false otherwise.
     */
    private function doesCommentIdExists($id, $comments)
    {
        $isIdExisting = false;

        if (isset($id) && !empty($comments)) {
            if (array_key_exists($id, $comments)) {
                $isIdExisting = true;
            }
        }

        return $isIdExisting;
    }

    /**
     * Edit comment.
     *
     * Checks if the specific index exist in the comments array in the session.
     * If it exists, it checks if a comment with the specified id is saved in
     * the session. If the id exists, the content in the session is updated.
     *
     * @param array $comment with all details. Comment array index and id is
     *                       needed.
     *
     * @return void
     */
    public function edit($comment)
    {
        if (!empty($comment)) {
            $comments = $this->session->get('comments', []);
            $pageKey = $comment['pageKey'];
            if (array_key_exists($pageKey, $comments)) {
                $id = $comment['id'];
                if ($this->doesCommentIdExists($id, $comments[$pageKey])) {
                    $comments[$pageKey][$id] = $comment;
                    $this->session->set('comments', $comments);
                }
            }
        }
    }

    /**
     * Delete comment.
     *
     * Checks if the specific index exist in the comments array in the session.
     * If it exists, it checks if comment contains an id and that id exists in
     * the comments that are saved in the session. If the id exists, the id and
     * content are removed.
     *
     * @param array $comment with all details. Comment array index and id is
     *                       needed.
     *
     * @return void
     */
    public function delete($comment)
    {
        if (!empty($comment)) {
            $comments = $this->session->get('comments', []);
            $pageKey = $comment['pageKey'];
            if (array_key_exists($pageKey, $comments)) {
                $id = $comment['id'];
                if ($this->doesCommentIdExists($id, $comments[$pageKey])) {
                    array_splice($comments[$pageKey], $id, 1);
                    $this->session->set('comments', $comments);
                }
            }
        }
    }
}
