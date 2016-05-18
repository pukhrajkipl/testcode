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
    public function delete($id = null) {
        $this->User->id = $id;
        if (!$this->User->exists()) {
            throw new NotFoundException(__('Invalid user'));
        }
        $this->request->allowMethod('post', 'delete');
        if ($this->User->delete()) {
            $this->Session->setFlash(__('The user has been deleted.'));
        } else {
            $this->Session->setFlash(__('The user could not be deleted. Please, try again.'));
        }
        return $this->redirect(array('action' => 'index'));
    }
    
    function forgotpassword(  ){
            //$this->User->set($this->request->data);
            if (  $this->request->is(array('post','put' )) ) {
                
               $check  = $this->User->find( 'first',array('conditions'=>array('User.email'=> $this->request->data['User']['email'] ),'fields'=>array('id')  ) );
               //pr($check); die;
               
               if( empty($check) ){
                   $this->Session->setFlash(__('This email not belongs to any profile!'), 'default', array(), 'warning');
               }else{
                $fpkey = $this->User->generatePassword();
                //pr($this->request->data); die;
                $email = trim($this->request->data['User']['email']);
                $this->User->updateAll(array( 'User.fpkey'=>"'".$fpkey."'" ),array('User.email'=>trim($this->request->data['User']['email'])  )  );
                $reset_password_link = Configure::read('Site.url') . "home/forgotpassword/" . $fpkey;
                $this->loadModel('EmailTemplate');
                $emailtemp = $this->EmailTemplate->findByEmailType('forgot_password_link');
                $emailtemp['EmailTemplate']['message'] = str_ireplace('[email]', $email, $emailtemp['EmailTemplate']['message']);
                $emailtemp['EmailTemplate']['message'] = str_ireplace('[password_change_url]', $reset_password_link, $emailtemp['EmailTemplate']['message']);

                $emailToSend = $emailtemp['EmailTemplate']['message'];

                $mail = new PHPMailer(); // create a new object
                $mail->IsSMTP(); // enable SMTP
                $mail->SMTPDebug = 1; // debugging: 1 = errors and messages, 2 = messages only
                $mail->SMTPAuth = true; // authentication enabled
                $mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for GMail
                $mail->Host = "smtp.gmail.com";
                $mail->Port = 465; // or 587
                $mail->IsHTML(true);
                $mail->Username = Configure::read('Smtp.email');
                $mail->Password = Configure::read('Smtp.password');
                
                $mail->SetFrom($emailtemp['EmailTemplate']['sender_email'],'Admin');
                $mail->Subject = $emailtemp['EmailTemplate']['subject'];
                $mail->Body = $emailToSend;
                $mail->AddAddress($email);
                //$emailSent = 1;
                if ($mail->Send()) {
                    $this->request->data['User'] = array();
                    $this->Session->setFlash(__('Reset password link successfully send to your email!'), 'default', array(), 'success');
                } else {
                    $this->Session->setFlash(__('Error in sending mail please try again!!'), 'default', array(), 'warning');
                }
               }
            } 
        $this->layout = 'common';
    }

    

}
