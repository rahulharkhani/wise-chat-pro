<div class="wcMessageQuoted" data-quote-id="{{ messageId }}" data-chat-user-id="{{ messageChatUserId }}">
    {% if avatarUrl %}
        <img class="wcMessageAvatar" src="{{ avatarUrl }}" alt="" />
    {% endif avatarUrl %}

    <span class="wcMessageUser" {% if isTextColorSetForUserName %}style="color:{{ textColor }}"{% endif isTextColorSetForUserName %}>
        {{ renderedUserName }}
    </span>
    <span class="wcMessageTime" data-utc="{{ messageTimeUTC }}"></span>
    <div class="wcMessageQuotedContent" {% if isTextColorSetForMessage %}style="color:{{ textColor }}"{% endif isTextColorSetForMessage %}>
        {{ messageContent }}
    </div>
</div>