<link rel='stylesheet' href='{{ themeStyles }}' type='text/css' media='all' />

<div id='{{ chatId }}' class='wcContainer {% if sidebarMode %} wcSidebarMode{% endif sidebarMode %} {% if windowTitle %}wcWindowTitleIncluded {% endif windowTitle %}' data-wc-pre-config="{{ jsOptionsEncoded }}">
	{% if showWindowTitle %}
		<div class='wcWindowTitle'>{{ windowTitle }}&#160;{% if sidebarMode %}<a href="javascript://" class="wcWindowTitleMinMaxLink"></a>{% endif sidebarMode %}</div>
	{% endif showWindowTitle %}

	<div class="wcWindowContent">
		<div class="wcUserNameHint">{{ messageEnterUserName }}</div>
		
		<form method="post" class="wcUserNameForm" action="{{ formAction }}">
			<input type="text" name="wcUserName" class="wcUserName" required />
			<input type="submit" value="{{ messageLogin }}" />
		</form>
		
		{% if authenticationError %}
			<div class='wcError wcUserNameError'>{{ authenticationError }}</div>
		{% endif authenticationError %}
	</div>
</div>

{{ cssDefinitions }}
{{ customCssDefinitions }}