Codeigniter-TwitterOauth library integration
============================================

Description
-----------

This is [TwitterOAuth PHP library](https://github.com/abraham/twitteroauth) by Abraham Williams slightly changed for Codeigniter integration as library.

More information about Codeigniter libraries available on [Codeigniter Documentation](http://codeigniter.com/user_guide).

Usage
-----

There are two ways to load library to Codeigniter. Automatically within **./config/autoload.php** or directly in your controller by adding this line:  

	$this->load->library('twitteroauth');

Create an instance:

	$connection = $this->twitteroauth->create($consumer, $consumer_secret, $access_token, $access_token_secret);

Verify your authentication details:

	$content = $connection->get('account/verify_credentials');

Send a message:

	$data = array(
		'status' => $message,
		'in_reply_to_status_id' => $in_reply_to
	);
	$result = $connection->post('statuses/update', $data);

More information about Twitter API methods available on [Twitter Developer Page](http://dev.twitter.com).

In addition there is sample authentication controller in the repository. Please refer to controller directory.

Feedback
--------

Library related issues should be send to [official issue tracker](https://github.com/abraham/twitteroauth/issues).

Any other question are welcome [here](https://github.com/MunGell/Codeigniter-TwitterOAuth/issues).