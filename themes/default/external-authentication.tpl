<link rel='stylesheet' href='{{ themeStyles }}' type='text/css' media='all' />

<div id='{{ chatId }}' class='wcContainer {% if sidebarMode %} wcSidebarMode {% endif sidebarMode %} {% if windowTitle %}wcWindowTitleIncluded{% endif windowTitle %}' data-wc-pre-config="{{ jsOptionsEncoded }}">
	{% if showWindowTitle %}
		<div class='wcWindowTitle'>{{ windowTitle }}&#160;{% if sidebarMode %}<a href="javascript://" class="wcWindowTitleMinMaxLink"></a>{% endif sidebarMode %}</div>
	{% endif showWindowTitle %}
	
	<div class="wcWindowContent">
		{% if anonymousLoginURL %}
			<input class='wcAnonymousLoginButton' type='button' value='{{ loginAnonymously }}' onclick="window.location.href = '{{ anonymousLoginURL }}'" />
		{% endif anonymousLoginURL %}

		{% if loginUsing %}
			<div class="wcExternalLoginHint wcBottomMargin">
				{{ loginUsing }}:
			</div>
		{% endif loginUsing %}

		<div class="wcExternalLoginButtons wcCenter">
			{% if facebookRedirectURL %}
				<a href="{{ facebookRedirectURL }}" class="wcFacebookLoginButton" title="Facebook sign in">Facebook</a>
			{% endif facebookRedirectURL %}

			{% if twitterRedirectURL %}
				<a href="{{ twitterRedirectURL }}" class="wcTwitterLoginButton" title="Twitter sign in">Twitter</a>
			{% endif twitterRedirectURL %}

			{% if googleRedirectURL %}
				<a href="{{ googleRedirectURL }}" class="wcGoogleLoginButton" title="Google sign in">Google</a>
			{% endif googleRedirectURL %}
		</div>
		
		{% if authenticationError %}
			<div class='wcError wcExternalAuthenticationError'>{{ authenticationError }}</div>
		{% endif authenticationError %}
	</div>
</div>

{{ cssDefinitions }}
{{ customCssDefinitions }}