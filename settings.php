<?php

	defined('MOODLE_INTERNAL') || die;

	if ($ADMIN->fulltree)
	{
		$settings->add(new admin_setting_heading('pluginname', '',
			new lang_string('auth_untissooauthdescription', 'auth_untissooauth')));

		$settings->add(new admin_setting_configtext(
			'auth_untissooauth/client_id',
			get_string('config_client_id', 'auth_untissooauth'),
			null,
			null,
			PARAM_TEXT));

		$settings->add(new admin_setting_configtext(
			'auth_untissooauth/client_secret',
			get_string('config_client_secret', 'auth_untissooauth'),
			null,
			null,
			PARAM_TEXT));

		$settings->add(new admin_setting_configtext(
			'auth_untissooauth/auth_url',
			get_string('config_auth_url', 'auth_untissooauth'),
			null,
			null,
			PARAM_TEXT));
	}
