<?php

	global $CFG, $DB;

	require( '../../config.php' );
	require_once( $CFG->dirroot . '/auth/untissooauth/vendor/autoload.php' );
	require_once( $CFG->dirroot . '/auth/untissooauth/classes/provider/UntiSSOProvider.php' );

	$code = optional_param('code', '', PARAM_TEXT);
	if (empty($code))
	{
		throw new moodle_exception('invalid_code_param', 'auth_untissooauth');
	}

	$state = optional_param('state', null, PARAM_TEXT);
	if (empty($state) || !isset($_SESSION[ 'unti_sso_oauth_state' ]) || ( $_SESSION[ 'unti_sso_oauth_state' ] !== $state ))
	{
		throw new moodle_exception('invalid_state_param', 'auth_untissooauth');
	}

	$provider = new UntiSSOProvider();
	$token = $provider->getAccessToken('authorization_code', [ 'code' => $code ]);

	$accessToken = $token->getToken();

	if (empty($accessToken))
	{
		throw new moodle_exception('could_not_get_access_token', 'auth_untissooauth');
	}

	$externalUser = $provider->getResourceOwner($token);

	$username = 'unti_' . $externalUser->id;

	$user = $DB->get_record('user', [ 'username' => $username, 'mnethostid' => $CFG->mnet_localhost_id ]);
	if (empty($user))
	{
		$user = create_user_record($username, '', 'untissooauth');

		if (!empty($externalUser->imageUrl))
		{
			$tempFilename = substr(microtime(), 0, 10) . '.tmp';
			$tempFolder = $CFG->tempdir . '/filestorage';

			$tempFile = $tempFolder . '/' . $tempFilename;

			if (copy($externalUser->imageUrl, $tempFile))
			{
				require_once( $CFG->libdir . '/gdlib.php' );
				$userIconID = process_new_icon(context_user::instance($user->id, MUST_EXIST), 'user', 'icon', 0, $tempFile);
				if ($userIconID)
				{
					$DB->set_field('user', 'picture', $userIconID, [ 'id' => $user->id ]);
				}
			}
		}
	}

	$dataToUpdate = array_merge([
		'id' => $user->id,
	], $externalUser->toArray());

	$DB->update_record('user', $dataToUpdate);

	$user = authenticate_user_login($username, null);
	complete_user_login($user);

	redirect($CFG->wwwroot . '/');