<?php
/**
 * Twitter OAuth library.
 * Sample controller.
 * Requirements: enabled Session library, enabled URL helper
 * Please note that this sample controller is just an example of how you can use the library.
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Twitter extends CI_Controller
{
	/**
	 * TwitterOauth class instance.
	 */
	private $connection;
	
	/**
	 * Controller constructor
	 */
	function __construct()
	{
		parent::__construct();
		// Loading TwitterOauth library. Delete this line if you choose autoload method.
		$this->load->library('twitteroauth');
		// Loading twitter configuration.
		$this->config->load('twitter');
		
		if($this->session->userdata('access_token') && $this->session->userdata('access_token_secret'))
		{
			// If user already logged in
			$this->connection = $this->twitteroauth->create($this->config->item('twitter_consumer_token'), $this->config->item('twitter_consumer_secret'), $this->session->userdata('access_token'),  $this->session->userdata('access_token_secret'));
		}
		elseif($this->session->userdata('request_token') && $this->session->userdata('request_token_secret'))
		{
			// If user in process of authentication
			$this->connection = $this->twitteroauth->create($this->config->item('twitter_consumer_token'), $this->config->item('twitter_consumer_secret'), $this->session->userdata('request_token'), $this->session->userdata('request_token_secret'));
		}
		else
		{
			// Unknown user
			$this->connection = $this->twitteroauth->create($this->config->item('twitter_consumer_token'), $this->config->item('twitter_consumer_secret'));
		}
	}
	
	/**
	 * Here comes authentication process begin.
	 * @access	public
	 * @return	void
	 */
	public function auth()
	{
		if($this->session->userdata('access_token') && $this->session->userdata('access_token_secret'))
		{
			// User is already authenticated. Add your user notification code here.
			redirect(base_url('/'));
		}
		else
		{
			// Making a request for request_token
			$request_token = $this->connection->getRequestToken(base_url('/twitter/callback'));

			$this->session->set_userdata('request_token', $request_token['oauth_token']);
			$this->session->set_userdata('request_token_secret', $request_token['oauth_token_secret']);
			
			if($this->connection->http_code == 200)
			{
				$url = $this->connection->getAuthorizeURL($request_token);
				redirect($url);
			}
			else
			{
				// An error occured. Make sure to put your error notification code here.
				redirect(base_url('/'));
			}
		}
	}
	
	/**
	 * Callback function, landing page for twitter.
	 * @access	public
	 * @return	void
	 */
	public function callback()
	{
		if($this->input->get('oauth_token') && $this->session->userdata('request_token') !== $this->input->get('oauth_token'))
		{
			$this->reset_session();
			redirect(base_url('/twitter/auth'));
		}
		else
		{
			$access_token = $this->connection->getAccessToken($this->input->get('oauth_verifier'));
		
			if ($this->connection->http_code == 200)
			{
				$this->session->set_userdata('access_token', $access_token['oauth_token']);
				$this->session->set_userdata('access_token_secret', $access_token['oauth_token_secret']);
				$this->session->set_userdata('twitter_user_id', $access_token['user_id']);
				$this->session->set_userdata('twitter_screen_name', $access_token['screen_name']);

				$this->session->unset_userdata('request_token');
				$this->session->unset_userdata('request_token_secret');
				
				redirect(base_url('/'));
			}
			else
			{
				// An error occured. Add your notification code here.
				redirect(base_url('/'));
			}
		}
	}
	
	public function post($in_reply_to)
	{
		$message = $this->input->post('message');
		if(!$message || mb_strlen($message) > 140 || mb_strlen($message) < 1)
		{
			// Restrictions error. Notification here.
			redirect(base_url('/'));
		}
		else
		{
			if($this->session->userdata('access_token') && $this->session->userdata('access_token_secret'))
			{
				$content = $this->connection->get('account/verify_credentials');
				if(isset($content->errors))
				{
					// Most probably, authentication problems. Begin authentication process again.
					$this->reset_session();
					redirect(base_url('/twitter/auth'));
				}
				else
				{
					$data = array(
						'status' => $message,
						'in_reply_to_status_id' => $in_reply_to
					);
					$result = $this->connection->post('statuses/update', $data);

					if(!isset($result->errors))
					{
						// Everything is OK
						redirect(base_url('/'));
					}
					else
					{
						// Error, message hasn't been published
						redirect(base_url('/'));
					}
				}
			}
			else
			{
				// User is not authenticated.
				redirect(base_url('/twitter/auth'));
			}
		}
	}
	
	/**
	 * Reset session data
	 * @access	private
	 * @return	void
	 */
	private function reset_session()
	{
		$this->session->unset_userdata('access_token');
		$this->session->unset_userdata('access_token_secret');
		$this->session->unset_userdata('request_token');
		$this->session->unset_userdata('request_token_secret');
		$this->session->unset_userdata('twitter_user_id');
		$this->session->unset_userdata('twitter_screen_name');
	}
}

/* End of file twitter.php */
/* Location: ./application/controllers/twitter.php */