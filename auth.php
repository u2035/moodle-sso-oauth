<?php

	if (!defined('MOODLE_INTERNAL'))
	{
		die('Direct access to this script is forbidden.');    // It must be included from a Moodle page.
	}

	global $CFG;

	require_once( $CFG->libdir . '/authlib.php' );
	require_once( $CFG->dirroot . '/auth/untissooauth/vendor/autoload.php' );
	require_once( $CFG->dirroot . '/auth/untissooauth/classes/provider/UntiSSOProvider.php' );

	class auth_plugin_untissooauth extends auth_plugin_base
	{
		private $provider;

		public function __construct ()
		{
			$this->authtype = 'untissooauth';
			$this->roleauth = 'auth_untissooauth';
			$this->errorlogtag = '[AUTH UNTISSOOAUTH] ';
			$this->config = get_config('auth_untissooauth');

			$this->provider = new UntiSSOProvider();
		}

		public function is_internal ()
		{
			return false;
		}

		public function loginpage_hook ()
		{
			global $CFG, $PAGE;

			if (!$this->provider->isEnabled)
			{
				return;
			}

			if (empty($_POST[ 'username' ]) && empty($_POST[ 'password' ]))
			{
				$PAGE->requires->jquery();
				$content = $this->provider->getAuthLinkHtml();
				$PAGE->requires->css('/auth/untissooauth/style.css');
				$PAGE->requires->js_init_code("unti_sso_oauth_button = '$content';");
				$PAGE->requires->js(new moodle_url($CFG->httpswwwroot . "/auth/untissooauth/script.js"));

				$_SESSION[ 'unti_sso_oauth_state' ] = $this->provider->getState();
			}
		}

		public function user_login ($username, $password)
		{
			global $DB, $CFG;

			$user = $DB->get_record('user', [ 'username' => $username, 'mnethostid' => $CFG->mnet_localhost_id ]);

			if (!empty($user) && ( $user->auth == 'untissooauth' ))
			{
				$code = optional_param('code', false, PARAM_TEXT);
				if (empty($code))
				{
					return false;
				}

				return true;
			}

			return false;
		}

		public function process_config ($config)
		{
			foreach ([ 'client_id', 'client_secret', 'auth_url' ] as $key)
			{
				if (!isset($config->{$key}))
				{
					$config->{$key} = '';
				}

				set_config($key, $config->{$key}, 'auth_untissooauth');
			}

			return true;
		}

		function get_userinfo ($username)
		{
			return [];
		}
	}