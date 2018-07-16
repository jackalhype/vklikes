@extends('layouts.main')

@section('content')
<div class="e404 container">    
    <p><strong>404</strong>, requested page not found.</p>
    <p>Misclicked ? <img class="emoji-cry emoji" src="/images/blank.gif"/></p>
    <p>Our VK Likes GEO service is <a href="http://{{env('APP_DOMAIN')}}"> here</a></p>
</div>

@endsection