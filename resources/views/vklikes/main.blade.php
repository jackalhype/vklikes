@extends('../layouts.main')

@section('title', 'VK Likes GEO')
@section('description', 'VK Likes GEO service. Find people from your Region or City who liked particular VK post.')

@section('content')

    <header class="container-fluid vk-likes-geo-header">
        <h1>VK Likes GEO</h1>
        <h4 class="sub-title"><i>Find people from your Region or City who liked particular VK post.</i></h4>
    </header>
    <br />
    <div class="container-fluid relative vk-likes-filters">
        <div class="bg" {{-- style="background: url(http://cs629231.vk.me/v629231001/c535/Aolq7Qohi2o.jpg) repeat -10px -30px / 160px 130px; opacity: 0.35;position: absolute; top:0; left:0;right:0; bottom:0" --}}></div>        
        <form class="ajax_form" id="vk-likes-form" method="GET" action="" onsubmit="VKL.pager.reinit(); VKL.showLikes('vk-likes-form');return false;" >            
            <input type="hidden" name="vklikes_request_id" id="vklikes_request_id"/>            
            <div class="form-group col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <label for="vk_url">Paste VK Post URL:</label>
                <input class="form-control" id="vk_url" name="vk_url" type="text" placeholder="https://vk.com/kinomania?w=wall-43215063_15499268" value="" onchange="VKL.setRequestId();console.log('vk_url:onchange');" />
            </div>
            <div class="clearfix"></div>
            <div class="form-group col-xs-12 col-sm-4 col-md-4 col-lg-4">
                <label for="country">Country:</label>
                <input class="form-control js-typeahead" id="country" name="country" type="text" placeholder="Russia" value="" />
                <input type="hidden" name="country_id" />
            </div>
            <div class="form-group col-xs-12 col-sm-4 col-md-4 col-lg-4">
                <label for="region">Region:</label>
                <input class="form-control js-typeahead" id="region" name="region" type="text" placeholder="" value="" />
                <input type="hidden" name="region_id" />
            </div>
            <div class="form-group col-xs-12 col-sm-4 col-md-4 col-lg-4">
                <label for="city">City:</label>
                <input class="form-control js-typeahead" id="city" name="city" type="text" placeholder="" value="" />
                <input type="hidden" name="city_id" />
            </div>
            <div class="form-group col-xs-12">
                <button class="btn btn-success top-buffer" type="button" onclick="VKL.pager.reinit(); VKL.showLikes('vk-likes-form');">View post likes</button>
            </div>
        </form>
    </div>
    <div class="container-fluid vk-likes-results-wrap">        
        <div class="vk-likes-results tiles" id="vk-likes-results"></div>
    </div>

@endsection
