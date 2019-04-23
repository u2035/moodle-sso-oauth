$(window).on('load',
	function () {
		if ($("#unti_sso_oauth_custom_location").length > 0) {
			$("#unti_sso_oauth_custom_location").append(unti_sso_oauth_button);
		} else {
			var formObj = $("input[name='username']").closest("form");
			if (formObj.length > 0) {
				$(formObj).each(function (i, formItem) {
					var username = $(formItem).find("input[name='username']").val();
					var password = $(formItem).find("input[name='password']").val();
					if (username !== "guest" || password !== "guest") {
						$(formItem).append(unti_sso_oauth_button);
					}
				});
			}
		}
	}
)
