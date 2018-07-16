(function($, window){

if (typeof VKL === 'undefined') {
    VKL = {};
}

$(function(){
    console.log('ready!');
    if ($('#vk-likes-form').length > 0) {
        VKL.initCommonBehaviors();
        VKL.init('vk-likes-form');
    }
    if ($('#smm-filters-form').length > 0) {
        VKL.initCommonBehaviors();
        VKL.init('smm-filters-form');
    }
    $('input[type=file]').bootstrapFileInput();
});

VKL.init = function(form_id) {
    VKL.pager = {};
    VKL.form_id = form_id;
    VKL.users_per_ajax = 30;
    VKL.pager.reinit = function() {
        VKL.pager.page = 1;
        VKL.pager.lastPage = false;
        VKL.pager.failsNum = 0;
    }
    VKL.pager.fail = function() {
        VKL.pager.failsNum++;
        if (VKL.pager.failsNum >= 3) {
            VKL.pager.lastPage = true;
        }
    }
    VKL.pager.reinit();
    VKL.SkrollHandler = new VKL.skrollHandlerConstructor('vk-likes-results', VKL.showLikes, VKL.form_id);
    VKL.reinit = function() {
        VKL.pager.reinit();
    }
}


VKL.setError = function($container, error) {
    $container.html('<span class="vk-likes-error">' + error + '</span>');
}

VKL.showLikes = function(form_id) {
    var $form = $('#' + form_id);
    var vk_url = $form.find('input[name="vk_url"]').val(),
        country_id = $form.find('input[name="country_id"]').val(),
        region_id = $form.find('input[name="region_id"]').val(),
        city_id = $form.find('input[name="city_id"]').val(),
        ajax_params,
        $results_container = $('#vk-likes-results');

    if (VKL.pager.lastPage) {
        return false;
    }
    VKL.setShowLikesIsBusy(true);
    ajax_params = {
        vk_url: vk_url,
        country_id: country_id,
        region_id: region_id,
        city_id: city_id,
        page: VKL.pager.page
    };
    if (VKL.getRequestId()) {
        ajax_params.vklikes_request_id = VKL.getRequestId();
    }
    if (VKL.pager.page == 1) {
        VKL.loader.show($results_container);
    } else {
        VKL.loader.append($results_container);
    };
    $.get('/ajax/showLikes', ajax_params,
        function(data, status, xhr){
            if (typeof data.error !== 'undefined') {
                VKL.pager.lastPage = true;
                VKL.setError($results_container, data.error);
                console.log(data.error);
                VKL.pager.fail();
                VKL.setShowLikesIsBusy(false);
                return false;
            }
            var response = data.response;
            if (!response || parseInt(data.items_num) < VKL.users_per_ajax) {
                VKL.pager.lastPage = true;
            }
            if (data.items_num > 0) {
                if (VKL.pager.page == 1) {
                    $results_container.html(response);
                } else {
                    $results_container.append(response);
                }
            } else {
                if (VKL.pager.page == 1) {
                    VKL.setError($results_container, 'No results found.')
                } else {
                    VKL.pager.lastPage = true;
                }
            }
            VKL.loader.hide($results_container);
            VKL.pager.page++;
            VKL.setRequestId(data.vklikes_request_id);
            VKL.setShowLikesIsBusy(false);
        }, 'json'
    ).fail(function(xhr, status, error) {
        console.log(error);
        VKL.pager.fail();
        VKL.setShowLikesIsBusy(false);
    });
}

VKL._showLikesIsBusy = false;
VKL.showLikesIsBusy = function() {
    return VKL._showLikesIsBusy;
}
VKL.setShowLikesIsBusy = function(val) {
    VKL._showLikesIsBusy = !!val;
}

VKL.skrollHandlerConstructor = function(container_id, ajax_func, form_id) {
    var _this = this,
        container_id = container_id,
        $container = $('#' + container_id);
    this.ajax_func = ajax_func;
    this.form_id = form_id;
    this.reinit = function() {
        $(window).on('scroll', function(){
            if (_this.needLoadMore()) {
                _this.ajax_func(_this.form_id);
            }
        });
    };

    this.needLoadMore = function(){
        if ($container.find('div').length == 0) {
            return false;
        }
        var container_bottom_pos = $container.outerHeight(true) + $container.position().top,
            window_height = $(window).height(),
            scroll_pos = $(window).scrollTop()
            ;
        if (1.5 * window_height + scroll_pos >= container_bottom_pos) {
            if (!VKL.showLikesIsBusy()) {
                return true;
            }
        }
        return false;
    }

    this.reinit();
}

VKL.getRequestId = function() {
    return $('#vklikes_request_id').val();
}

VKL.setRequestId = function(val) {
    var val = val ? val : '';
    $('#vklikes_request_id').val(val);
}

VKL.filterIds = function(form_id) {
    var $form = $('#' + form_id);
    var ajax_params = new FormData($form[0]),
        $results_container = $('#vk-likes-results');
    $.ajax({
        url: '/ajax/filterIds',
        type: 'POST',
        data: ajax_params,
        contentType: false,
        processData: false,
        success: function(data, status, xhr){

        }
    });
    return false;
}

VKL.initCommonBehaviors = function () {
    VKL.initTooltips();

    VKL.initTypeahead({
        name: 'country',
        id_field: 'country_id',
        plural: 'countries'
    });

    VKL.initTypeahead({
        name: 'region',
        id_field: 'region_id',
        plural: 'regions',
        form_params: [
            {
                name: 'country_id'
            }
        ]
    });

    VKL.initTypeahead({
        name: 'city',
        id_field: 'city_id',
        plural: 'cities',
        form_params: [
            {
                name: 'country_id'
            },
            {
                name: 'region_id'
            }
        ]
    });
}

VKL.initTooltips = function() {
    $('[data-toggle="tooltip"]').tooltip({
        delay: {
            show: 700,
            hide: 400,
        },
    });
}

VKL.loader = {
    loader_html: '<div class="loader"></div>',
    background_html: '<div class="loader-background"></div>',
    show: function($container) {
        var $loader = $(this.loader_html);
        $loader.addClass('ontop');
        $container.append($loader);
        $container.append($(this.background_html));
    },
    append: function($container) {
        $container.append($(this.loader_html));
    },
    hide: function($container) {
        $container.find('.loader').remove();
        $container.find('.loader-background').remove();
    }
}

/**
 * required params: name, id_field, plural
 * optional params: form_params[{name}]
 */
VKL.initTypeahead = function(params) {
    $fields = $('.js-typeahead[name="'+params.name+'"]').each(function() {
        var $field = $(this);
        var $id_field = $field.siblings('input[name="'+params.id_field+'"]');
        var $form = $field.closest('form');
        var lang = $form.find('input[name="lang"]').val();
        $field.typeahead({
            hint: true,
            highlight: true,
            minLenght: 1,
            classNames: {}
        }, {
            name: '',
            limit: 12,
            async: true,
            display: function(value) {
                return ''+ value.title;
            },
            templates: {
                notFound: [
                    '<div class="typeahead-empty-message">',
                        'Unable to find any '+ params.plural.ucfirst()+' that match the current query.',
                    '</div>'
                ].join('\n')
            },
            source: function(q, sync, async) {
                var ajax_params = {q: q}, form_param_name, $form_param;
                if (lang) {
                    ajax_params.lang = lang;
                }
                if (typeof params.form_params !== 'undefined') {
                    for (i in params.form_params) {
                        form_param_name = params.form_params[i].name;
                        $form_param = $form.find('input[name="'+form_param_name+'"]');
                        ajax_params[form_param_name] = $form_param.val();
                    }
                }
                return $.get('/ajax/vk/'+params.plural+'.get',
                    ajax_params,
                    function(response, status) {
                        return async(response[params.plural]);
                    }, 'json'
                );
            }
        })
        .bind('typeahead:select', function(event, suggestion) {
            console.log('select', suggestion);
            $id_field.val(suggestion[params.id_field]);
        })
        .bind('typeahead:cursorchange', function(event, suggestion) {
            console.log('cursorchange', suggestion);
            $id_field.val(suggestion[params.id_field]);
        })
        .bind('typeahead:change', function(event, title) {
            if (!title) {
                $id_field.val('');
            }
            VKL.reinit();
        });
    });
}

})(jQuery, window)


String.prototype.ucfirst = function() {
    return this.charAt(0).toUpperCase() + this.slice(1);
}

