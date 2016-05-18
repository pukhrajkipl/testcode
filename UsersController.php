<?php
App::uses('AppController', 'Controller');
/**
 * Users Controller
 *
 * @property User $User
 * @property PaginatorComponent $Paginator
 */
class UsersController extends AppController {
    /**
     * Components
     *
     * @var array
     */
    public $components = array('Paginator');

    
    
    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow('logout','forgotpassword');
        
        App::import('Vendor', 'PHPMailer', array('file' => 'PHPMailer/class.phpmailer.php'));
        App::import('Vendor', 'SMTP', array('file' => 'PHPMailer/class.smtp.php'));
        //$this->Auth->deny('test');
    }
    
    
    public function logout() {//die('d');
        $this->Session->setFlash('Logout Successful.','default',array('class'=>'success'));
        $this->Auth->logout();
        $this->redirect(array('controller' => 'users', 'action' => 'login'));
    }
    
    /**
     * index method
     *
     * @return void
     */
    public function index() {
        $this->User->recursive = 0;
        $this->set('users', $this->Paginator->paginate());
    }

    public function login() {
       $this->redirect('/');
    }
    
    
    function signup(){
        $this->request->data = array( 
                                'User'=>array(
                                    'username'=>'t1',
                                    'firstname'=>'t1',
                                    'lastname'=>'t2',
                                    'dob'=>'1985-05-04',
                                    'city_id'=>'5',
                                    'email'=>'t1@gmgi.com',
                                    'password'=>'123456',
                                    
                                ) 
            );  
        
        
        $this->User->save($this->request->data);
    }
    
    /**
     * view method
     *
     * @throws NotFoundException
     * @param string $id
     * @return void
     */
    public function view($id = null) {
        if (!$this->User->exists($id)) {
            throw new NotFoundException(__('Invalid user'));
        }
        $options = array('conditions' => array('User.' . $this->User->primaryKey => $id));
        $this->set('user', $this->User->find('first', $options));
    }

    /**
     * add method
     *
     * @return void
     */
    public function add() {
        if ($this->request->is('post')) {
            $this->User->create();
            if ($this->User->save($this->request->data)) {
                $this->Session->setFlash(__('The user has been saved.'));
                return $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('The user could not be saved. Please, try again.'));
            }
        }
        $roles = $this->User->Role->find('list');
        $this->set(compact('roles'));
    }

    /**
     * edit method
     *
     * @throws NotFoundException
     * @param string $id
     * @return void
     */
    public function edit($id = null) {
        if (!$this->User->exists($id)) {
            throw new NotFoundException(__('Invalid user'));
        }
        if ($this->request->is(array('post', 'put'))) {
            if ($this->User->save($this->request->data)) {
                $this->Session->setFlash(__('The user has been saved.'));
                return $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('The user could not be saved. Please, try again.'));
            }
        } else {
            $options = array('conditions' => array('User.' . $this->User->primaryKey => $id));
            $this->request->data = $this->User->find('first', $options);
        }
        $roles = $this->User->Role->find('list');
        $this->set(compact('roles'));
    }

    /**
     * delete method
     *
     * @throws NotFoundException
     * @param string $id
     * @return void
     */

}
