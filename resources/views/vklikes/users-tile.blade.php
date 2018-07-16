@foreach ($vk_users as $vk_user)
<div class="item tile col-xs-4 col-sm-3 col-md5-1 col-lg-2  vk-user-tile js-vk-user-tile">
    <div class="vk-user-image" style="background-image: url({{ $vk_user->photo_big }});"></div>
    <div class="vk-user-info">
        <a target="_blank" href="https://vk.com/{{ $vk_user->domain }}"><span class="vk-user-name">{{ $vk_user->first_name }} {{ $vk_user->last_name }}</span></a>
    </div>
</div>
@endforeach
