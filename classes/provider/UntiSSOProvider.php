<?php

	global $CFG;

	defined('MOODLE_INTERNAL') || die();

	require_once( $CFG->dirroot . '/auth/untissooauth/classes/models/UntiResourceOwner.php' );
	require_once( $CFG->dirroot . '/auth/untissooauth/vendor/autoload.php' );

	class UntiSSOProvider extends League\OAuth2\Client\Provider\AbstractProvider
	{
		use League\OAuth2\Client\Tool\BearerAuthorizationTrait;

		/** @var bool */
		public $isEnabled = true;

		/** @var string */
		private $authUrl = null;
		/** @var string */
		private $tokenUrl = null;
		/** @var string */
		private $apiBaseUrl = null;
		/** @var string */
		private $returnUrl = null;

		/**
		 * Constructor.
		 *
		 * @throws Exception
		 * @throws dml_exception
		 */
		public function __construct ()
		{
			global $CFG;

			$clientId = get_config('auth_untissooauth', 'client_id');
			$clientSecret = get_config('auth_untissooauth', 'client_secret');
			$authUrl = get_config('auth_untissooauth', 'auth_url');

			if (empty($clientId) || empty($clientSecret) || empty($authUrl))
			{
				$this->isEnabled = false;
			}

			parent::__construct([
				'clientId'     => $clientId,
				'clientSecret' => $clientSecret,
				'redirectUri'  => $CFG->wwwroot . '/auth/untissooauth/redirect.php',
			]);

			$this->authUrl = $authUrl . '/oauth2/authorize';
			$this->tokenUrl = $authUrl . '/oauth2/access_token';
			$this->apiBaseUrl = $authUrl . '/users/me';
			$this->returnUrl = $CFG->wwwroot . '/auth/untissooauth/redirect.php';
		}

		/**
		 * @inheritDoc
		 */
		protected function getAuthorizationHeaders ($token = null)
		{
			if ($token !== null)
			{
				return [ 'Authorization' => 'Bearer ' . $token->getToken() ];
			}

			return [];
		}

		public function getAuthLinkHtml ()
		{
			$url = $this->getAuthorizationUrl();

			return '<a class="btn btn-primary unti-login-button" href="' . $url . '">' . get_string('login_with_unti', 'auth_untissooauth') . '</a>';
		}

		/**
		 * Returns the base URL for authorizing a client.
		 *
		 * Eg. https://oauth.service.com/authorize
		 *
		 * @return string
		 */
		public function getBaseAuthorizationUrl ()
		{
			return $this->authUrl;
		}

		/**
		 * Returns the base URL for requesting an access token.
		 *
		 * Eg. https://oauth.service.com/token
		 *
		 * @param array $params
		 * @return string
		 */
		public function getBaseAccessTokenUrl (array $params)
		{
			return $this->tokenUrl;
		}

		/**
		 * Returns the URL for requesting the resource owner's details.
		 *
		 * @param \League\OAuth2\Client\Token\AccessToken $token
		 * @return string
		 */
		public function getResourceOwnerDetailsUrl (\League\OAuth2\Client\Token\AccessToken $token)
		{
			return $this->apiBaseUrl;
		}

		/**
		 * Returns the default scopes used by this provider.
		 *
		 * This should only be the scopes that are required to request the details
		 * of the resource owner, rather than all the available scopes.
		 *
		 * @return array
		 */
		protected function getDefaultScopes ()
		{
			return [];
		}

		/**
		 * Checks a provider response for errors.
		 *
		 * @param \Psr\Http\Message\ResponseInterface $response
		 * @param array|string                        $data Parsed response data
		 * @return void
		 * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
		 */
		protected function checkResponse (\Psr\Http\Message\ResponseInterface $response, $data)
		{
			if ($response->getStatusCode() !== 200)
			{
				throw new moodle_exception('invalid_response', 'auth_untissooauth');
			}
		}

		/**
		 * Generates a resource owner object from a successful resource owner
		 * details request.
		 *
		 * @param array                                   $response
		 * @param \League\OAuth2\Client\Token\AccessToken $token
		 * @return \League\OAuth2\Client\Provider\ResourceOwnerInterface
		 */
		protected function createResourceOwner (array $response, \League\OAuth2\Client\Token\AccessToken $token)
		{
			return new UntiResourceOwner($response);
		}
	}
