@extends('../layouts.main')

@section('title', 'SMM')
@section('description', 'Фильтры на группы ретаргетинга Вконтате. По ГЕО, полу, возрасту и другим параметрам.')

@section('content')

    <header class="container-fluid vk-likes-geo-header">
        <h1>Фильтры по группам ретаргетинга Вконтакте</h1>
        <h4 class="sub-title"><i>Точная настройка по ГЕО, полу, возрасту и другим параметрам.</i></h4>
    </header>
    <br />

    <div class="container smm-filters" id="smm-filters">
        <form id="smm-filters-form" class="smm-filters-form" method="POST" onsubmit="VKL.pager.reinit(); VKL.filterIds('smm-filters-form');return false;" enctype="multipart/form-data" action="javascript:;">
            <input type="hidden" name="lang" value="ru" />
            <input type="hidden" name="smm_request_id" id="smm_request_id"/>
            <div class="form-group col-xs-12 col-sm-6">
                <label>Введите список id, по 1 на строку</label>
                <textarea class="form-control ids-textarea" name="ids-list-screen" placeholder="" rows="6"></textarea>
            </div>
            <div class="form-group col-xs-12  col-sm-6">
                <label>Или загрузите файл со списком id: </label>
                <input name="ids-list-file" type="file" data-filename-placement="inside" title="Выберите файл" />
            </div>
            <div class="form-group col-xs-12 col-sm-6">
                <label class="radio-inline" style="padding-left: 0;"><b>Пол: </b></label>
                <label class="radio-inline"><input type="radio" name="sex" value="0" checked="checked">Любой</label>
                <label class="radio-inline"><input type="radio" name="sex" value="1">Мужской</label>
                <label class="radio-inline"><input type="radio" name="sex" value="2">Женский</label>
            </div>
            <div class="form-group col-xs-12 col-sm-6">
                <label for="age_from" class="select-label">Возраст: </label>
                <select name="age_from" id="age_from" class="marg-l-15">
                    <option value="0">Любой</option>
                    @for ($i=16; $i<=80; $i++)
                    <option value="{{ $i }}">от {{ $i }}</option>
                    @endfor
                </select>
                <select name="age_to" id="age_to" class="marg-l-15">
                    <option value="200">Любой</option>
                    @for ($i=16; $i<=80; $i++)
                    <option value="{{ $i }}">до {{ $i }}</option>
                    @endfor
                </select>
            </div>
            <div class=" form-group col-xs-12 col-sm-6">
                <label for="photos_min" class="select-label">Фотографий: </label>
                <select name="photos_min" id="photos_min" class="marg-l-15">
                    @for ($i=1; $i<=10; $i++)
                    <option value="{{ $i }}">от {{ $i }}</option>
                    @endfor
                </select>
            </div>

            <div class="clearfix"></div>
            <div class="form-group col-xs-12 col-sm-4 col-md-4 col-lg-4">
                <label for="country">Страна:</label>
                <input class="form-control js-typeahead" id="country" name="country" type="text" placeholder="Россия" value="" />
                <input type="hidden" name="country_id" />
            </div>
            <div class="form-group col-xs-12 col-sm-4 col-md-4 col-lg-4">
                <label for="region">Регион:</label>
                <input class="form-control js-typeahead" id="region" name="region" type="text" placeholder="" value="" />
                <input type="hidden" name="region_id" />
            </div>
            <div class="form-group col-xs-12 col-sm-4 col-md-4 col-lg-4">
                <label for="city">Город:</label>
                <input class="form-control js-typeahead" id="city" name="city" type="text" placeholder="" value="" />
                <input type="hidden" name="city_id" />
            </div>
            <div class="form-group col-xs-12">
                <button class="btn btn-success top-buffer" type="button" onclick="VKL.pager.reinit(); VKL.filterIds('smm-filters-form');">Применить фильтры к списку</button>
            </div>
        </form>
    </div>
    <div class="container-fluid vk-likes-results-wrap">
        <div class="vk-likes-results tiles" id="vk-likes-results"></div>
    </div>


@endsection
