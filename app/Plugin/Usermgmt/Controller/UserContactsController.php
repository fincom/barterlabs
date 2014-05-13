<?php
/*
Cakephp 2.x User Management Premium Version (a product of Ektanjali Softwares Pvt Ltd)
Website- http://ektanjali.com
Plugin Demo- http://umpremium.ektanjali.com
Author- Chetan Varshney (The Director of Ektanjali Softwares Pvt Ltd)
Plugin Copyright No- 11498/2012-CO/L

UMPremium is a copyrighted work of authorship. Chetan Varshney retains ownership of the product and any copies of it, regardless of the form in which the copies may exist. This license is not a sale of the original product or any copies.

By installing and using UMPremium on your server, you agree to the following terms and conditions. Such agreement is either on your own behalf or on behalf of any corporate entity which employs you or which you represent ('Corporate Licensee'). In this Agreement, 'you' includes both the reader and any Corporate Licensee and Chetan Varshney.

The Product is licensed only to you. You may not rent, lease, sublicense, sell, assign, pledge, transfer or otherwise dispose of the Product in any form, on
a temporary or permanent basis, without the prior written consent of Chetan Varshney.

The Product source code may be altered (at your risk)

All Product copyright notices within the scripts must remain unchanged (and visible).

If any of the terms of this Agreement are violated, Chetan Varshney reserves the right to action against you.

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Product.

THE PRODUCT IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE PRODUCT OR THE USE OR OTHER DEALINGS IN THE PRODUCT.
*/

App::uses('UserMgmtAppController', 'Usermgmt.Controller');
App::uses('CakeEmail', 'Network/Email');
class UserContactsController extends UserMgmtAppController {
	/**
	 * This controller uses following models
	 *
	 * @var array
	 */
	public $uses = array('Usermgmt.UserContact', 'Usermgmt.User');
	/**
	 * This controller uses following components
	 *
	 * @var array
	 */
	public $components = array('RequestHandler', 'Usermgmt.Search');
	/**
	 * This controller uses following helpers
	 *
	 * @var array
	 */
	public $helpers = array('Js');
	/**
	 * This controller uses following default pagination values
	 *
	 * @var array
	 */
	public $paginate = array(
		'limit' => 25
	);
	/**
	 * This controller uses search filters in following functions for ex index, online function
	 *
	 * @var array
	 */
	var $searchFields = array
		(
			'index' => array(
				'UserContact' => array(
					'UserContact'=> array(
						'type' => 'text',
						'label' => 'Search',
						'tagline' => 'Search by username, email, subject, body',
						'condition' => 'multiple',
						'searchFields'=>array('UserContact.username', 'UserContact.email', 'UserContact.subject', 'UserContact.body'),
						'inputOptions'=>array('style'=>'width:300px;')
					)
				)
			)
		);
	/**
	 * Called before the controller action.  You can use this method to configure and customize components
	 * or perform logic that needs to happen before each controller action.
	 *
	 * @return void
	 */
	public function beforeFilter() {
		parent::beforeFilter();
		if(isset($this->Security) &&  ($this->RequestHandler->isAjax() || $this->action=='contactUs')){
			$this->Security->csrfCheck = false;
			$this->Security->validatePost = false;
		}
	}
	/**
	 * It displays all contacts enquiries
	 *
	 * @access public
	 * @return array
	 */
	public function index() {
		$this->paginate = array('limit' => 10, 'order'=>'UserContact.id desc');
		$userContacts = $this->paginate('UserContact');
		$this->set('userContacts', $userContacts);
		if($this->RequestHandler->isAjax()) {
			$this->layout = 'ajax';
			$this->render('/Elements/all_contacts');
		}
	}
	/**
	 * It is used to show contact enquiry form
	 *
	 * @access public
	 * @return void
	 */
	public function contactUs() {
		$userId = $this->UserAuth->getUserId();
		if($userId) {
			$user = $this->User->getUserById($userId);
		}
		if ($this->request->isPost()) {
			if(USE_RECAPTCHA && !$this->RequestHandler->isAjax()) {
				$this->request->data['UserContact']['captcha']= (isset($this->request->data['recaptcha_response_field'])) ? $this->request->data['recaptcha_response_field'] : "";
			}
			$this->UserContact->set($this->request->data);
			$contactValidate = $this->UserContact->contactValidate();
			if($this->RequestHandler->isAjax()) {
				$this->layout = 'ajax';
				$this->autoRender = false;
				if ($contactValidate) {
					$response = array('error' => 0, 'message' => 'success');
					return json_encode($response);
				} else {
					$response = array('error' => 1,'message' => 'failure');
					$response['data']['UserContact']  = $this->UserContact->validationErrors;
					return json_encode($response);
				}
			} else {
				if ($contactValidate) {
					$this->request->data['UserContact']['user_id'] = $userId;
					$this->UserContact->save($this->request->data,false);
					$this->__sendMailToAdmin($this->request->data['UserContact']['username'], $this->request->data['UserContact']['email'], $this->request->data['UserContact']['subject'], $this->request->data['UserContact']['body']);
					$this->Session->setFlash(__('Thank you for contacting us. We will be in touch with you very soon!'));
					$this->redirect(array('controller'=>'Users', 'action'=>'myprofile', 'plugin'=>'usermgmt'));
				}
			}
		} else {
			if(!empty($user)) {
				$this->request->data['UserContact']['username'] = $user['User']['username'];
				$this->request->data['UserContact']['email'] = $user['User']['email'];
				
			}
		}
	}


/**
	 * It is used to show contact enquiry form
	 *
	 * @access public
	 * @return void
	 */
	public function bugReport() {
		$userId = $this->UserAuth->getUserId();
		if($userId) {
			$user = $this->User->getUserById($userId);
		}
		if ($this->request->isPost()) {
			if(USE_RECAPTCHA && !$this->RequestHandler->isAjax()) {
				$this->request->data['UserContact']['captcha']= (isset($this->request->data['recaptcha_response_field'])) ? $this->request->data['recaptcha_response_field'] : "";
			}
			$this->UserContact->set($this->request->data);
			$contactValidate = $this->UserContact->contactValidate();
			if($this->RequestHandler->isAjax()) {
				$this->layout = 'ajax';
				$this->autoRender = false;
				if ($contactValidate) {
					$response = array('error' => 0, 'message' => 'success');
					return json_encode($response);
				} else {
					$response = array('error' => 1,'message' => 'failure');
					$response['data']['UserContact']  = $this->UserContact->validationErrors;
					return json_encode($response);
				}
			} else {
				if ($contactValidate) {
					$this->request->data['UserContact']['user_id'] = $userId;
					$this->UserContact->save($this->request->data,false);
					$this->__sendMailToAdmin($this->request->data['UserContact']['username'], $this->request->data['UserContact']['email'], $this->request->data['UserContact']['subject'], $this->request->data['UserContact']['body']);
					$this->Session->setFlash(__('Thank you for contacting us. We will be in touch with you very soon!'));
					$this->redirect(array('controller'=>'Users', 'action'=>'myprofile', 'plugin'=>'usermgmt'));
				}
			}
		} else {
			if(!empty($user)) {
				$this->request->data['UserContact']['username'] = $user['User']['username'];
				$this->request->data['UserContact']['email'] = $user['User']['email'];
				
			}
		}
	}












	/**
	 * It is used to send contact enquiry mail to admin
	 *
	 * @access private
	 * @param string $name entered name in enquiry form
	 * @param string $email entered email in enquiry form
	 * @param string $phone entered subject in enquiry form
	 * @param string $requirement entered requirement in enquiry form
	 * @return void
	 */
	private function __sendMailToAdmin($username, $email, $subject, $body) {
		$emailObj = new CakeEmail('default');
		$emailObj->emailFormat('both');
		$fromConfig = EMAIL_FROM_ADDRESS;
		$fromNameConfig = EMAIL_FROM_NAME;
		$emailObj->from(array($fromConfig => $fromNameConfig));
		$emailObj->sender(array($fromConfig => $fromNameConfig));
		$emailObj->subject(__('Contact Enquiry'));
		$body = nl2br($body);
		$body=__('Hi Admin, <br/><br/>A contact enquiry has been made. Here are the details- <br/><br/>Username- %s <br/>Email- %s <br/>Subject- %s <br/>Body- %s <br/><br/>Thanks', $username, $email, $subject, $body);
		if(ADMIN_EMAIL_ADDRESS) {
			$emailObj->to(ADMIN_EMAIL_ADDRESS);
			try{
				$result = $emailObj->send($body);
			} catch (Exception $ex) {
				// we could not send the email, ignore it
				$result="Could not send contact enquiry mail to admin";
				$this->log($result, LOG_DEBUG);
			}
		}
	}
}