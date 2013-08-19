
/**
 * persistant variable set
 * @param array language_strings an array of type Language_string, the Language_string object holds all important data of the LANG strings
 * @param array taged_strings is a collection of all strings which have pseudo tags, used for checkup in the submision proces
 * @param int first_shown marks the first shown element of the translation submision form
 * @param int last_shown marks the last shown element of the translation submision form
 */
 var language_strings = new Array();
 var taged_strings;
 var first_shown = 0;
 var last_shown = 6;
 var script_name='string_extract';

 $(document).ready(function()
 {

    add_autocomplete_to_language_input();

    //check if the language is already selected if it is the selection form will not be shown
    var language = '';
    language = getCookie('selected_language');
    if (language != '' && language != null) {
        hide_language_input();
        get_original_language_values();
    }

    //handles the language selection
    $('#translator_form').submit(function(event)
    {
        event.preventDefault();
        document.cookie = 'selected_language = ' + $('#translator_language').val();
        get_original_language_values();
        hide_language_input();

    });

    //handles submition of translations
    $('#translator').on('submit', '#translator_form_submission', function(event)
    {
        event.preventDefault();
        translator_form_submit();
    });

});


/*
 * Gets list of available languages for translation via AJAX call
 * and uses jQueryUI to create autocomplete option for the language selection input
 */
 function add_autocomplete_to_language_input()
 {
    var r_url = get_base_url();
    r_url += "/get_languages.php";

    var ajaxRequest = $.ajax({
        url: r_url
    });

    ajaxRequest.done(function(response, textStatus, jqKHR)
    {
        var languages_object = JSON.parse(response);
        var languages_name = [];
        for (var key in languages_object) {
            if (languages_object.hasOwnProperty(key)) {
                languages_name.push(languages_object[key]);
            }
        }

        $("#translator_language").autocomplete({
            source: languages_name
        });
    });
}


/*##############################################################################
 ## The next part of the script handles picking and re-picking of the language ##
 ## Creating the translation <form> - Geting values from the database such as  ##
 ## strings, pseudo tags, votes                                                ##
 #############################################################################*/

/*if a language for translation if picked by the user
* the form is hidden */
function hide_language_input()
{
    $('#change_language').prepend("<label id='selected_language'>"
        + " Selected language: <a class='change_selected' href='javascript:void(0)' onclick='show_language_input()'> (change) </a> " + "<span class='selected'>" + getCookie('language') + "</span> </label> ");
    $('#translator_form').addClass('hidden');
}

/* if the user wants to change the picked language
 * this function will show the translation form again and reset navigation variables
 */
 function show_language_input()
 {
    $('#translator_form').val('');
    $('#translator_form').removeClass('hidden');
    $('#submission_form').html('');

    first_shown = 0;
    last_shown = 5;

    $('#selected_language').remove();
}
/* Sends a AJAX request to get formated LANG strings
 * and the acctual translation form
 */
 function get_original_language_values()
 {
    var r_url = get_base_url();
    r_url += "get_original_language_values.php";

    for(var i=0; i<language_strings.length; i++){
        language_strings[i].string='';
    }


    var language = getCookie('selected_language');
    var html = $('html').text();
    var ajaxRequest = $.ajax({
        url: r_url,
        data: { language: language, url: location.host + location.pathname, html: html  },
        type: "POST"
    });

    ajaxRequest.done(function(response, textStatus, jqKHR)
    {

        var response_object = JSON.parse(response);
        var error_code = response_object['error_code'];

        if(error_code >= 1){
            error_handler();
            return;
        }
        var form = response_object['form'];

        language_strings = response_object['language_array'];
        taged_strings = response_object['taged_strings'];
        show_progress_bar(response_object['translated']);
        
        if ($('#translator_form').size() > 0){
            $('#submission_form').append(form); 
        }
    });

    ajaxRequest.fail(function(jqXHR, textStatus, errorThrown)
    {
        console.log(jqXHR);
        var error = "<div class='error' > There has been an error retrieving the data.";
        error += "If this persists contact the site admin or <a href='mailto: b.ttalic@gmail.com?Subject=Translator%20Plugin%20Error'>b.ttalic</a></div>";
        $('#submission_form').append(error);

    });
}


function error_handler()
{
    var error = "<div class='error' > There has been an error retrieving the data.";
    error += "If this persists contact the site admin or <a href='mailto: b.ttalic@gmail.com?Subject=Translator%20Plugin%20Error'>b.ttalic</a></div>";
    $('#submission_form').append(error);
}

/*Will show a graphical representation of the amount of translated
* strings to the current language */
function show_progress_bar(translated)
{
    translated = parseFloat(translated).toFixed(2);
    var not_translated = parseFloat(100 - translated).toFixed(2);
    $('.translator .progress_bar #translated').width(translated + '%').html(translated + '%');
    $('.translator .progress_bar #not_translated').width(not_translated + '%').html(not_translated + '%');
}



/*##############################################################################
 ## The next part of the script handles submiting of translations, voting and  ##
 ## highlighting strings on the page. It contains navigation functions as well ##
 #############################################################################*/

/*The call is handled via AJAX
 * after the response is sent faulty inputs, if any will be marked as such
 * the user is notified about successfully saved inputs, those input boxes will be removed
 */
 function translator_form_submit()
 {
    var r_url = get_base_url();
    r_url += "submit_translation.php";

    var ajaxRequest = $.ajax({
        url: r_url,
        data: $('#translator_form_submission').serialize() + '&taged_strings=' + JSON.stringify(taged_strings) + '&count=' + language_strings.length,
        type: "post"
    });

    ajaxRequest.done(function(response, textStatus, jqXHR)
    {

        var response_object = JSON.parse(response);
        var bad_inputs = response_object['bad_input'];
        var good_inputs = response_object['good_input'];
        var translated = response_object['translated'];
        var awards = response_object['awards_number']

        mark_bad_inputs(bad_inputs);
        remove_submited(good_inputs);
        if( awards > 0)
            add_notification(awards);


    });

    ajaxRequest.fail(function(jqXHR, textStatus, errorThrown)
    {
        console.log(textStatus);
    });
}


/*Adds a css class to faulty inputs so they are easiy recognisible, Gives a previev of those at the begining of the form
 * * @param array bad_inputs array of numbers marking the input id of the faulty inputs
 */
 function mark_bad_inputs(bad_inputs)
 {
    var error_message = "<ul> You forgot the &lttag&gt/&ltvar&gt in following translations:";
    for (var i = 0; i < bad_inputs.length; i++) {
        $('#translator_form_submission #input_span_' + bad_inputs[i]).addClass('bad_input');
        error_message += "<li>" + language_strings[bad_inputs[i]].string + "</li>";
    }
    error_message += '</ul>';
    if (bad_inputs.length > 0)
        $('#submision_error').html(error_message);
}

/** removes successfully submited inputs
 * @param array good_inputs array of numbers marking the input id of the successfully saved inputs
 */
 function remove_submited(good_inputs)
 {
    for (var i = 0; i < good_inputs.length; i++) {
        $('#translator_form_submission #input_span_' + good_inputs[i]).remove();
    }
    if (good_inputs.length > 0)
        $('#submision_success').html("Successfully submited: " + good_inputs.length + " translation(s)!</br> Thank You!");

}

/**
* Adds notification to side view if awards have been given
* @param int award number of awards given
*/
function add_notification(awards)
{
    $('.notification_badge').html(awards);
    var tooltip_content = "You have " + awards + " new badge(s)!!! Check them out!";
    $('.notification_badge').parent().attr('title', tooltip_content);
    $('.notification_badge').parent().attr('title', tooltip_content);
}


/** A request is sent to mark the vote in the database
 * if the translation is deleted because of too many bad votes (currently 5) the translation is deleted and the page reloaded
 * othervise the object which made the request is highlighted and disabled
 * @param int sign the vote -1 or 1 depending on user choice
 * @param string id the id of the language_string associated with the string which is voted
 * @param object object the object which made the call
 */
 function vote(sign, id, object)
 {
    r_url = get_base_url();
    r_url += "vote.php";

    var ajaxRequest = $.ajax({
        url: r_url,
        data: {sign: sign, translation_id: language_strings[id].translation_id},
        type: "POST"
    });

    ajaxRequest.done(function(response, textStatus, jqKHR)
    {
        var response_object = JSON.parse(response);
        if (response_object['refresh']) {
            location.reload();
        } else {
            var pair = '';
            if (object.id.indexOf('down') >= 0) {
                pair = '#' + object.id.replace('down', 'up');
            }
            else
                pair = '#' + object.id.replace('up', 'down');
            $(object).attr('disabled', '');
            $(pair).removeAttr('disabled');
        }
    });

    ajaxRequest.fail(function(jqXHR, textStatus, errorThrown)
    {
        console.log(textStatus);
    });
}

/*Will show the next 6 (or less) input fields of the translation form
 If neccessary disables the arrow for showing next translations
 */
 function show_next()
 {
    var count = 0;

    $('#input_span_' + last_shown).nextAll('span[id^=input_span_]').slice(0, 6).each(function()
    {
        $(this).removeClass('temp_hidden');
        count++;

    });
    if (count < 6)
        $('#down_img').addClass('hidden');


    $('#input_span_' + last_shown).prevAll('span[id^=input_span_]').slice(0, 6).each(function()
    {
        $(this).addClass('temp_hidden');
    });
    last_shown += count;
    first_shown += count;
    $('#up_img').removeClass('hidden');
}

/*Will show the previous 6 (or less) input fields of the translation form
 If neccessary disables the arrow for showing previous translations
 */
 function show_previous() {
    var count = 0;

    $('#input_span_' + first_shown).nextAll('span[id^=input_span_]').slice(0, 6).each(function()
    {
        $(this).addClass('temp_hidden');
        count++;

    });

    $('#input_span_' + first_shown).prevAll('span[id^=input_span_]').slice(0, 6).each(function()
    {
        $(this).removeClass('temp_hidden');
    });
    last_shown -= count;
    first_shown -= count;
    if (first_shown <= 0)
        $('#up_img').addClass('hidden');

    $('#down_img').removeClass('hidden');
}



/**
 * adds CSS class to highligh selected string(s) on page
 */
 function highlight()
 {
    var id = event.target.id;
    id = id.replace('_image', '');
    var value = $('label[for="' + id + '"]').html();
    id = id.replace("translator_input_", "");
    console.log(value);
    console.log(id);
    console.log(language_strings[id].parsed);
    regexp = new RegExp(language_strings[id].parsed, "g");
        html = document.documentElement.innerHTML;
    console.log(regexp);
    console.log(regexp.exec(html));
    html = html.replace(regexp, "<span class='translator_highlighted'>"+language_strings[id].parsed+"</span>");

//console.log(html);
//console.log(regexp.exec($('html').text()));
document.documentElement.innerHTML = html;
}
/**
 * removes CSS class of highlighted string(s) on page
 */
 function remove_highlight()
 {
    var id = event.target.id;
    id = id.replace('_image', '');
    var value = $('#' + id + "_hidden").val();

    var class_name = '.' + value;
    $(class_name).each(function() {
        $(this).removeClass('translator_highlighted');
    });
}



/*##############################################################################
 ## The next part of the script is a set of helper functions used by the       ##
 ## above code                                                                 ##
 #############################################################################*/


 /* Creates the base url for AJAX calls and resource retreival (e.g. images) */
 function get_base_url()
 {   
     var scripts = document.getElementsByTagName('script'),
     len = scripts.length,
     re = script_name+'.js',
     src, r_url;

     while (len--) {
      src = scripts[len].src;
      if (src && src.match(re)) {
        r_url = src;
        break;
    }
}
r_url = r_url.substring(0, r_url.indexOf(script_name));  
return r_url.toLowerCase();
}

/*Gets the script saved cookie
 http://www.w3schools.com/js/js_cookies.asp
 */
 function getCookie(c_name)
 {
    var c_value = document.cookie;
    var c_start = c_value.indexOf(" " + c_name + "=");
    if (c_start == -1)
    {
        c_start = c_value.indexOf(c_name + "=");
    }
    if (c_start == -1)
    {
        c_value = null;
    }
    else
    {
        c_start = c_value.indexOf("=", c_start) + 1;
        var c_end = c_value.indexOf(";", c_start);
        if (c_end == -1)
        {
            c_end = c_value.length;
        }
        c_value = unescape(c_value.substring(c_start, c_end));
    }
    return c_value;
}


/*Shows guidelines for CrowdTranslator usage*/
function show_guidelines()
{

    var translator = $('#translator');
    var r_url = get_base_url();
    r_url += '/images/'
    var close_url = r_url + 'close.png';
    var highlight_url = r_url + 'highlight.png';
    var remove_highlight_url = r_url + 'rhighlight.png';
    var vote_up = r_url + 'vote_up.png';
    var vote_down = r_url + 'vote_down.png';
    var up = r_url + 'up.png';
    var down = r_url + 'down.png';

    var display = "<div id='translator_guidelines' style='height:" + translator.height() + "px; width:" + translator.width() + "px; top:" + translator.position().top + "px'> ";
    display += "<span > <img src= '" + close_url + "' onclick='hide_guidelines()' class='form_image'/>";
    display += "<div id='translator_guidelines_inner'>";
    display += "<ul>";
    display += "<li> Click <img src= '" + highlight_url + "'/> To <span class='translator_highlighted'>highlight</span> the string on the page. </li>";
    display += "<li> Highlight will not work if the string is inside title, value etc tags </li> ";
    display += "<li> Click <img src= '" + remove_highlight_url + "' /> To remove highlight from strings on the page. </li>";
    display += "<li> Click <img src= '" + vote_up + "' /> To vote up a translation you think is good. </li>";
    display += "<li> Click <img src= '" + vote_down + "' /> To vote down a translation you think is bad. </li>";
    display += "<li> If a string has &lttag&gt or &ltvar&gt in it writte them in an appropriate place in the translation </li>";
    display += "<li> Click <img src= '" + up + "'/> To show next inputs </li>";
    display += "<li> Click <img src= '" + down + "'/> To show previous inputs </li>";

    display += "</ul>";
    display += "</div>";
    display += "</div>";
    $('#translator').append(display);

}
/* Hides guidlines for translator usage */
function hide_guidelines()
{
    $('#translator_guidelines').remove();
}

/**
 * Shows content of hidden divs
 * @param string id the id of the function calles
 */
 function show(id)
 {
    var element = $('#' + id + '_content');
    if (element.hasClass('hidden')) {
        element.removeClass('hidden');
        $('#' + id).html('(hide)');
    } else {
        element.addClass('hidden');
        $('#' + id).html('(show)');
    }

}
/**
 * Issues AJAX call to retrieve next/previous translations, change order of translations or increase translations display per page
 * @param int limit number of translations per page
 * @param int start first translation to be shown
 * @param int admin indicading if admin mode or user mode
 * @param string order_by indicating the ordering of the table
 */
 function show_more_translations(limit, start, admin, order_by)
 {

    var limit_input = $('#limit').val();

    if (!isNaN(limit_input)) {
        limit = limit_input;
        strat = -1;
    }

    if (limit == 0)
        limit = null;


    r_url = get_base_url();
    r_url += "lib-translator.php";

    var function_name = 'get_user_translations_table';
    if (admin == 1)
        function_name = 'get_translations_table';

    var ajaxRequest = $.ajax({
        url: r_url,
        data: {function: function_name, limit: limit, start: start, order_by: order_by},
        type: "POST"
    });

    ajaxRequest.done(function(response, textStatus, jqKHR) {
        $('#user_translations').html(response);
    });

    ajaxRequest.fail(function(jqXHR, textStatus, errorThrown) {
        console.log(textStatus);
    });

}

/**
 * Issuing AJAX call to delete a translation from the database
 * @param int id The translation id
 * @param string translation the translation text
 */
 function delete_translation(id, translation)
 {

    r_url = get_base_url();
    r_url += "lib-translator.php";

    var confirmation = confirm("Are you sure you want to delete '" + translation + "' ?");

    if (!confirmation) {
        return;
    }

    var ajaxRequest = $.ajax({
        url: r_url,
        data: {function: 'delete_translation', id: id},
        type: "POST"
    });

    ajaxRequest.done(function(response, textStatus, jqKHR) {
        $('#translation_' + id).remove();
    });

    ajaxRequest.fail(function(jqXHR, textStatus, errorThrown) {
        console.log(textStatus);
    });

}

/**
 * Issues AJAX call to display all available badges
 * @param int admin Indicating if showing all badges awarded to a user or all badges awailable
 */
 function get_all_badges(admin)
 {

    var link_value = $('#badges_show').html();
    if (link_value == '(show all)') {

        r_url = get_base_url();
        r_url += "lib-translator.php";

        var ajaxRequest = $.ajax({
            url: r_url,
            data: {function: 'get_user_badges', admin: admin},
            type: "POST"
        });

        ajaxRequest.done(function(response, textStatus, jqKHR)
        {

            $('#badges_display').html(response);
            $('#badges_show').html('(hide)');
        });

        ajaxRequest.fail(function(jqXHR, textStatus, errorThrown)
        {
            console.log(textStatus);
        });

    } else {
        $('#badges_show').html('(show all)');
        $('#badges_display').children().each(function(index)
        {
            if (index > 3)
                $(this).remove();
        });
    }

}

/**
 * Changing the number of translations shown per page
 * @see show_more_translations
 */
 function translation_table_change_limit()
 {
    var limit = $('#limit').val();
    var script = $('#show_next').attr("onclick");
    script = script.substring(script.indexOf('('));
        var params = script.split(',');

        var admin = parseInt(params[2]);
        var order_by = params[3];
        order_by = order_by.replace(')', '');
        show_more_translations(limit, -1, admin, order_by);
    }

/**
 * Issuing AJAX call to put a user on the block list
 */
 function block_user(user_id)
 {
    r_url = get_base_url();
    r_url += "lib-translator.php";

    var confirmation = confirm("Are you sure you want to block this user from translating ? Blocking a user will also delete all of his translations");

    if (!confirmation) {
        return;
    }

    var ajaxRequest = $.ajax({
        url: r_url,
        data: {function: 'block_user', user_id: user_id},
        type: "POST"
    });

    ajaxRequest.done(function(response, textStatus, jqKHR) {
        location.reload();
    });

    ajaxRequest.fail(function(jqXHR, textStatus, errorThrown) {
        console.log(textStatus);
    });

}

/**
 * Issuing AJAX call to remove a user from the block list
 */
 function remove_block(user_id)
 {
    r_url = get_base_url();
    r_url += "lib-translator.php";

    var confirmation = confirm("Are you sure you want to un-block this user?");

    if (!confirmation) {
        return;
    }

    var ajaxRequest = $.ajax({
        url: r_url,
        data: {function: 'remove_block', user_id: user_id},
        type: "POST"
    });

    ajaxRequest.done(function(response, textStatus, jqKHR) {
        location.reload();
    });

    ajaxRequest.fail(function(jqXHR, textStatus, errorThrown) {
        console.log(textStatus);
    });

}

function escapeRegExp(str) {
  return str.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
}