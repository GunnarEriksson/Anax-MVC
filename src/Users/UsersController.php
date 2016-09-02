<?php
namespace Anax\Users;

/**
 * A controller for users and admin related events.
 *
 */
class UsersController implements \Anax\DI\IInjectionAware
{
    use \Anax\DI\TInjectable;

    /**
     * Initialize the controller.
     *
     * @return void
     */
    public function initialize()
    {
        $this->di->session();

        $this->users = new \Anax\Users\User();
        $this->users->setDI($this->di);
    }

    /**
     * List all users.
     *
     * @return void
     */
    public function listAction()
    {
        $all = $this->users->findAll();
        $table = $this->createTable($all);

        $this->theme->setTitle("Användare");
        $this->views->add('users/index', ['content' => $table, 'title' => "Användare", 'subtitle' => "Visa alla användare"], 'main')
                    ->add('users/userAdmin', ['title' => "Användare", 'subtitle' => 'Administration'], 'sidebar');
    }

    private function createTable($data) {
        $table = new \Guer\HTMLTable\CHTMLTable();

        $tableSpecification = [
            'id'        => 'users',
        ];

        $table = $table->create($tableSpecification, $data, [
            'id' => [
                'title' => 'Id',
                'function'	=> function($link) {
                    return '<a href="users/id/'. $link . '">' . $link . '</a>';
                }
            ],
            'acronym' => [
                'title'    => 'Akronym',
            ],
            'name' => [
                'title'    => 'Namn',
            ],
            'object1' => [
                'title'    => 'Status',
                'function'	=> function($user) {
                    $status = "";
                    if (isset($user->deleted)) {
                        $status = '<i class="fa fa-user fa-fw" style="color:black"></i>';
                    } else {
                        if (isset($user->active)) {
                            $status = '<i class="fa fa-user fa-fw" style="color:green"></i>';
                        } else {
                            $status = '<i class="fa fa-user fa-fw" style="color:grey"></i>';
                        }
                    }

                    return $status;
                }
            ],
            'object2' => [
                'title'    => 'Redigera',
                'function'	=> function($user) {
                    $edit = '<a href="users/update/' . $user->id . '"><i class="fa fa-pencil-square-o" style="color:green" aria-hidden="true"></i></a>';
                    if (isset($user->deleted)) {
                        $delete = '<a href="users/delete/' . $user->id . '"><i class="fa fa-trash-o" style="color:red" aria-hidden="true"></i></a>';
                    } else {
                        $delete = '<a href="users/softDelete/' . $user->id . '"><i class="fa fa-trash-o" style="color:red" aria-hidden="true"></i></a>';
                    }

                    return $edit . " " . $delete;
                }
            ],
        ]);

        return $table->getHTMLTable();

    }

    /**
     * List user with id.
     *
     * @param int $id of user to display
     *
     * @return void
     */
    public function idAction($id = null)
    {
        $user = $this->users->find($id);

        if ($user) {
            $this->theme->setTitle("View user with id");
            $this->views->add('users/view', [
                'title' => 'Användare',
                'subtitle' => 'Användareinformation',
                'user' => $user,
            ]);
        } else {
            $content = [
                'subtitle' => 'Hittar ej användare',
                'message' =>  'Hittar ej användare med id: ' . $id
            ];

            $this->showNoSuchUserMessage($content);
        }


    }

    /**
     * Helper function for initiate no such user view.
     *
     * Initiates a view which shows a message the user with the specfic
     * id is not found. Contains a return button.
     *
     * @param  [] $content the subtitle and the message shown at page.
     *
     * @return void
     */
    private function showNoSuchUserMessage($content)
    {
        $this->theme->setTitle("View user with id");
        $this->views->add('error/errorInfo', [
            'title' => 'Användare',
            'subtitle' => $content['subtitle'],
            'message' => $content['message'],
        ], 'main');
    }

    /**
     * Add new user.
     *
     * @param string $acronym of user to add.
     *
     * @return void
     */
    public function addAction($acronym = null)
    {
        $form = new \Anax\HTMLForm\Users\CFormAddUser();
        $form->setDI($this->di);
        $status = $form->check();

        $this->di->theme->setTitle("Lägg till användare");
        $this->di->views->add('users/userForm', [
            'title' => "Användare",
            'subtitle' => "Lägg till användare",
            'content' => $form->getHTML(),
        ], 'main');

        $info = $this->di->fileContent->get('users/addUserInfo.md');
        $info = $this->di->textFilter->doFilter($info, 'shortcode, markdown');

        $this->di->views->add('users/userInfo', [
            'content' => $info,
        ], 'sidebar');
    }

    /**
     * Update user
     *
     * @param integer $id of user to delete.
     *
     * @return void
     */
    public function updateAction($id = null)
    {
        $user = $this->users->find($id);

        if ($user) {

            $form = new \Anax\HTMLForm\Users\CFormUpdateUser($user->getProperties());
            $form->setDI($this->di);
            $status = $form->check();

            $this->di->theme->setTitle("Uppdatera användare");
            $this->di->views->add('users/userForm', [
                'title' => "Användare",
                'subtitle' => "Uppdatera användare",
                'content' => $form->getHTML(),
            ], 'main');

            $info = $this->di->fileContent->get('users/updateUserInfo.md');
            $info = $this->di->textFilter->doFilter($info, 'shortcode, markdown');

            $this->di->views->add('users/userInfo', [
                'content' => $info,
            ], 'sidebar');
        } else {
            $content = [
                'subtitle' => 'Hittar ej användare',
                'message' =>  'Hittar ej användare med id: ' . $id
            ];

            $this->showNoSuchUserMessage($content);
        }
    }

    /**
     * Delete user.
     *
     * @param integer $id of user to delete.
     *
     * @return void
     */
    public function deleteAction($id = null)
    {
        $user = $this->users->find($id);

        if ($user) {

            $form = new \Anax\HTMLForm\Users\CFormDeleteUser($user->getProperties());
            $form->setDI($this->di);
            $status = $form->check();

            //$info = $this->di->fileContent->get('users-editinfo.md');
            //$info = $this->di->textFilter->doFilter($info, 'shortcode, markdown');

            $this->di->theme->setTitle("Radera användare");
            $this->di->views->add('users/userForm', [
                'title' => "Användare",
                'subtitle' => "Radera användare",
                'content' => $form->getHTML(),

               ], 'main');

            $info = $this->di->fileContent->get('users/deleteUserInfo.md');
            $info = $this->di->textFilter->doFilter($info, 'shortcode, markdown');

            $this->di->views->add('users/userInfo', [
                'content' => $info,
            ], 'sidebar');
        } else {
            $content = [
                'subtitle' => 'Hittar ej användare',
                'message' =>  'Hittar ej användare med id: ' . $id
            ];

            $this->showNoSuchUserMessage($content);
        }
    }

    /**
     * Delete (soft) user.
     *
     * @param integer $id of user to delete.
     *
     * @return void
     */
    public function softDeleteAction($id = null)
    {
        $user = $this->users->find($id);

        if ($user) {

            $form = new \Anax\HTMLForm\Users\CFormSoftDeleteUser($user->getProperties());
            $form->setDI($this->di);
            $status = $form->check();

            $this->di->theme->setTitle("Ta bort användare");
            $this->di->views->add('users/userForm', [
                'title' => "Användare",
                'subtitle' => "Ta bort användare",
                'content' => $form->getHTML(),
            ], 'main');

            $info = $this->di->fileContent->get('users/softDeleteUserInfo.md');
            $info = $this->di->textFilter->doFilter($info, 'shortcode, markdown');

            $this->di->views->add('users/userInfo', [
                'content' => $info,
            ], 'sidebar');
        } else {
            $content = [
                'subtitle' => 'Hittar ej användare',
                'message' =>  'Hittar ej användare med id: ' . $id
            ];

            $this->showNoSuchUserMessage($content);
        }
    }

    /**
     * List all active and not deleted users.
     *
     * @return void
     */
    public function activeAction()
    {
        $allActive = $this->users->query()
            ->where('active IS NOT NULL')
            ->andWhere('deleted is NULL')
            ->execute();

        $this->theme->setTitle("Aktiva användare");
        $this->views->add('users/index', [
            'users' => $allActive,
            'title' => "Användare",
            'subtitle' => "Visa aktiva användare"
        ], 'main');

        $this->views->add('users/userAdmin', ['title' => "Användare", 'subtitle' => 'Administration'], 'sidebar');
    }

    /**
     * List all inactive users.
     *
     * @return void
     */
    public function inactiveAction()
    {
        $allInactive = $this->users->query()
            ->where('active is NULL')
            ->andWhere('deleted is NULL')
            ->execute();

        $this->theme->setTitle("Inaktiva användare");
        $this->views->add('users/index', [
            'users' => $allInactive,
            'title' => "Användare",
            'subtitle' => "Visa inaktiva användare"
        ], 'main');

        $this->views->add('users/userAdmin', ['title' => "Användare", 'subtitle' => 'Administration'], 'sidebar');
    }

    /**
     * List all discarded users.
     *
     * @return void
     */
    public function discardedAction()
    {
        $allInactive = $this->users->query()
            ->where('deleted IS NOT NULL')
            ->execute();

        $this->theme->setTitle("Användare i papperskorgen");
        $this->views->add('users/index', [
            'users' => $allInactive,
            'title' => "Användare",
            'subtitle' => "Visa användare i papperskorgen"
        ], 'main');

        $this->views->add('users/userAdmin', ['title' => "Användare", 'subtitle' => 'Administration'], 'sidebar');
    }

    /**
     * Reset database to inital values
     *
     *
     */
    public function resetDbAction()
    {
        $this->db->dropTableIfExists('user')->execute();

        $this->db->createTable(
            'user',
            [
                'id' => ['integer', 'primary key', 'not null', 'auto_increment'],
                'acronym' => ['varchar(20)', 'unique', 'not null'],
                'email' => ['varchar(80)'],
                'name' => ['varchar(80)'],
                'password' => ['varchar(255)'],
                'created' => ['datetime'],
                'updated' => ['datetime'],
                'deleted' => ['datetime'],
                'active' => ['datetime'],
            ]
        )->execute();

        $this->db->insert(
            'user',
            [
                'acronym',
                'email',
                'name',
                'password',
                'created',
                'active'
            ]
        );

        $now = gmdate('Y-m-d H:i:s');

        $this->db->execute(
            [
                'admin',
                'admin@dbwebb.se',
                'Administrator',
                password_hash('admin', PASSWORD_DEFAULT),
                $now,
                $now
            ]
        );

        $url = $this->url->create('users');
        $this->response->redirect($url);
    }
}
