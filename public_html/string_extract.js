 /**
* persistant variable set
*/
var ajaxReturn = false;
var languages_name = [];
var languages_object = [];
var language_strings = new Array();

$(document).ready(function() {
    re_render_html();
    additional_purge();
    add_autocomplete_to_language_input();
    get_original_language_values();
    
    $('#translator_form').submit(function(event) {
        event.preventDefault();
        document.cookie = 'selected_language = ' + $('#translator_language').val();
        hide_language_input();
        location.reload();
    });

});

function hide_language_input() {
    $('#translator_form_submission').removeClass('hidden');
    $('#language_select').addClass('hidden');
    $('#translator_language').addClass('hidden');
    $('#translator_language_label').addClass('hidden');
    $('#translator_form').append("<label id='selected_language'> Selected language: " + "<span class='selected'>" + getCookie('language') + "</span>" 
    + "<a class='change_selected' href='#' onclick='show_language_input()'> change </a> </label>");
}

function show_language_input() {
    $('#translator_form_submission').addClass('hidden');
    $('#language_select').removeClass('hidden');
    $('#translator_language').removeClass('hidden');
    $('#translator_language').val(getCookie('language'));
    $('#translator_language_label').removeClass('hidden');
    $('#selected_language').remove();
}


/*
* retrieves the HTML of the current page
* removes all identifiers the plugin made for strings
* returns the clean html to the browser
*/
function re_render_html() {
    var html = $("html").html();
    
    while (html.indexOf('_-start_') > -1) {
        
        var start_point = html.indexOf("_-start_") + 8;
        
        var end_point = html.indexOf("_-end_");

        //extracting the original LANG string and the metadata
        var data = html.substring(start_point, end_point);
        var new_object = new Language_string(data)
        
        add_to_language_array(new_object);
        
        data = "_-start_" + data + "_-end_";
        while (html.indexOf(data) > -1) {
            html = remove_identificators(html, data, false, new_object);
        }
    }
    document.documentElement.innerHTML = html;
}


/* Creates a object from the data passed by the LANG arrays
 *@param data string the extracted data from the rendered page
 */
function Language_string(data) {
    this.array_name = extract_array_name(data);
    this.array_index = extract_array_index(data);
    this.string = extract_string(data);
    this.metadata = metadata(this.array_name, this.array_index);
    
    function extract_array_name(data) {
        var start_point = data.indexOf("array__") + 7;
        var end_point = data.indexOf("index__");
        var array_name = data.substring(start_point, end_point);
        return array_name;
    }
    ;
    
    function extract_array_index(data) {
        var start_point = data.indexOf("index__") + 7;
        var end_point = data.indexOf("||", data.indexOf("||") + 2);
        var array_index = data.substring(start_point, end_point);
        return array_index;
    }
    ;
    
    function extract_string(data) {
        var start_point = data.indexOf("||", data.indexOf("||") + 2) + 2;
        var string = data.substring(start_point, data.length);
        return string;
    }
    ;
    
    function metadata(array_name, array_index) {
        var meta = "array_" + array_name + "index_" + array_index;
        meta = meta.replace('$', '');
        return meta;
    }
    
    this.equals = function equals(other_language_string) {
        if ((this.array_name == other_language_string.array_name) && (this.array_index == other_language_string.array_index) && (this.string == other_language_string.string))
            return true;
        else
            return false;
    };

}


//Make sure every element is unique before adding it to the array
function add_to_language_array(element) {
    for (var i = 0; i < language_strings.length; i++) {
        if (language_strings[i].equals(element)) {
            return;
        }
    }
    language_strings.push(element);
}

/*
*removes identificators from the html, if appropriate adds <span>
*@param html string the html of the current page
*@param data string the extracted data part
*@param isFirst boolean true if first occurence of the string
*@param new_object object the object created from the data parameter
*@return returns the purged html
*/

function remove_identificators(html, data, isFirst, new_object) {
    //need the offset to make sure that the new <span> is not added to html tags such as <title>
    var offset = 50;
    if (html.indexOf(data) < 50) {
        offset = html.indexOf(data);
    }
    var test_string = html.substring(html.indexOf(data) - offset, html.indexOf(data) + data.length);
    
    var isTag = true;
    var flags = ["<title>", "value=", "title=", "alt=", "onclick="];
    for (var i = 0; i < flags.length; i++) {
        if (test_string.indexOf(flags[i]) > 0)
            isTag = false;
    }
    
    if (isFirst == true) {
        
        if (isTag) {
            html = html.replace("_-start_", "<span class='" + new_object.metadata + "'>");
            html = html.replace("_-end_", "</span>");
        } else {
            html = html.replace("_-start_", "");
            html = html.replace("_-end_", "");
        }
    
    } else {
        
        if (isTag)
            html = html.replace(data, "<span class='" + new_object.metadata + "'>" + new_object.string + "</span>");
        else
            html = html.replace(data, new_object.string);
    }
    
    return html;
}




/**
*In case there has been a oversee in removing identificators
* this function will take care of them
*/
function additional_purge() {
    for (var i = 0; i < language_strings.length; i++) {
        if (language_strings[i].string.indexOf("_-start_") > 0) {
            language_strings[i].string = language_strings[i].string.substring(0, language_strings[i].string.indexOf("_-start_"));
        }
    }
}

function get_image_url() {
    var r_url = window.location.pathname;
    r_url = r_url.substring(0, r_url.indexOf('public_html') + 11);
    r_url += '/CrowdTranslator/images/';
    return r_url;
}

/**
* Creates the <form> for the plugin bases on strings retrieved from the current page
* combined with markup created on installation of plugin
* finally all HTML and PHP variables will be dispalayed as <tag>
*/
function createForm() {
    var r_url = get_image_url();
    var highlight_url = r_url + 'highlight.png';
    var remove_highlight_url = r_url + 'rhighlight.png';
    var vote_up = r_url + 'vote_up.png';
    var vote_down = r_url + 'vote_down.png';
    var up = r_url + 'up.png';
    var down = r_url + 'down.png';
    
    var form = "<form id='translator_form_submission' name='translator_form_submission' class='hidden'>";
    form += "<span><img id='up_img' src='" + up + "' onclick='show_previous()' class='hidden navigation_images' /></span></br>";
    var template_label = "<label for='translator_input_COUNT'>STRING</label>";
    template_label += " <img class='form_image' src='" + remove_highlight_url + "' id='translator_input_COUNT' onclick=remove_highlight() />";
    template_label += " <img class='form_image' src='" + highlight_url + "' id='translator_input_COUNT' onclick=highlight() />";
    
    var template1 = "<input id='translator_input_COUNT' name='translator_input_COUNT' />";
    
    var template2 = "<div class='suggested'> <span id='translator_input_COUNT' >   TRANSLATION </span> <span class='votes'> <img src='" + vote_up + "' onclick='vote(1, translator_input_COUNT)' />  <img src='" + vote_down + "' /> </span> </div>";
    
    var template_hidden = "<input id='translator_input_COUNT_hidden' class='hidden' name='translator_input_COUNT_hidden' value='METADATA' />";
    
    
    if ($('#translator_form').size() > 0) {
        var hidden = false;
        for (var i = 0; i < language_strings.length; i++) {
            
            if (i > 5) {
                hidden = true;
            }
            
            
            
            
            if (language_strings[i].translation && language_strings[i].translation.length > 0) {
                
                if (hidden == true)
                    var template = "<span id='input_span_COUNT' class='group_input temp_hidden'>" + template_label + template2 + '<label > or enter your own: </label>' + template1 + template_hidden + "</span>";
                else
                    var template = "<span id='input_span_COUNT' class='group_input'>" + template_label + template2 + '<label > or enter your own: </label>' + template1 + template_hidden + "</span>";
                
                var nex_input = template.replace(/COUNT/g, i);
                nex_input = nex_input.replace(/STRING/g, language_strings[i].string);
                nex_input = nex_input.replace(/TRANSLATION/g, language_strings[i].translation);
                
                nex_input = nex_input.replace(/METADATA/g, language_strings[i].metadata);
            
            } else {
                
                if (hidden == true)
                    var template = "<span id='input_span_COUNT' class='temp_hidden'>" + template_label + template1 + template_hidden + "</span>";
                else
                    var template = "<span id='input_span_COUNT' >" + template_label + template1 + template_hidden + "</span>";
                
                var nex_input = template.replace(/COUNT/g, i);
                nex_input = nex_input.replace(/STRING/g, language_strings[i].string);
                
                nex_input = nex_input.replace(/METADATA/g, language_strings[i].metadata);
            }
            
            form += nex_input;
        }
        form += "<span><img id='down_img' src='" + down + "' onclick='show_next()' class='navigation_images' /></span>";
        form += "<input type='submit' />";
        form += '</form>';
        $('#submission_form').append(form);
    }
}

var first_shown = 0;
var last_shown = 5;

function show_next() {
    $('#up_img').removeClass('hidden');
    var count = 0;
    for (var i = first_shown; i < last_shown; i++) {
        $('#input_span_' + i).addClass('temp_hidden');
        first_shown++;
    
    }
    
    for (var i = last_shown; i < last_shown + 5; i++) {
        if ($('#input_span_' + i).size() > 0)
            $('#input_span_' + i).removeClass('temp_hidden');
        else {
            $('#down_img').addClass('disabled');
            break;
        }
        count++;
    }
    
    $('#up_img').removeClass('disabled');
    last_shown += count;
    if ($('#input_span_' + (last_shown + 1)).size() == 0)
        $('#down_img').addClass('hidden');
}

function show_previous() {
    $('#down_img').removeClass('hidden');
    var count = 0;
    
    for (var i = last_shown; i > first_shown; i--) {
        $('#input_span_' + i).addClass('temp_hidden');
    
    }
    
    for (var i = first_shown; i > first_shown - 6; i--) {
        if ($('#input_span_' + i).size() > 0)
            $('#input_span_' + i).removeClass('temp_hidden');
        else {
            $('#up_img').addClass('hidden');
            break;
        }
        count++;
    }
    
    $('#up_img').removeClass('disabled');
    last_shown -= count;
    first_shown -= count;
    if ($('#input_span_' + (last_shown - 1)).size() == 0)
        $('#up_img').addClass('hidden');
}


/**
* adds CSS class to highligh selected string(s) on page
*/
function highlight() {
    var id = event.target.id;
    var value = $('#' + id + "_hidden").val();
    
    var class_name = '.' + value;
    $(class_name).each(function() {
        $(this).addClass('translator_highlighted');
    });
}
/**
* removes CSS class of highlighted string(s) on page
*/
function remove_highlight() {
    var id = event.target.id;
    var value = $('#' + id + "_hidden").val();
    
    var class_name = '.' + value;
    $(class_name).each(function() {
        $(this).removeClass('translator_highlighted');
    });
}

/*
* Gets list of available languages for translation via AJAX call
* and uses jQueryUI to create autocomplete option for the language selection input
*/
function add_autocomplete_to_language_input() {
    var r_url = window.location.pathname;
    r_url = r_url.substring(0, r_url.indexOf("public_html"));
    r_url += 'plugins/crowdtranslator/get_languages.php'
    
    var ajaxRequest = $.ajax({
        url: r_url
    });
    
    ajaxRequest.done(function(response, textStatus, jqKHR) {
        languages_object = JSON.parse(response);
        
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

function get_original_language_values() {
    var r_url = window.location.pathname;
    r_url = r_url.substring(0, r_url.indexOf("public_html"));
    r_url += 'plugins/crowdtranslator/get_original_language_values.php'
    var json_ob = JSON.stringify(language_strings);
    
    var ajaxRequest = $.ajax({
        url: r_url,
        data: {objects: json_ob,language: getCookie('language')},
        type: "POST"
    });
    
    ajaxRequest.done(function(response, textStatus, jqKHR) {
        language_strings = JSON.parse(response);
        for (var i = 0; i < language_strings.length; i++) {
            while (language_strings[i]['string'].indexOf('<') >= 0) {
                language_strings[i].string = language_strings[i].string.replace('<', "&lt");
            }
            while (language_strings[i]['string'].indexOf('>') >= 0) {
                language_strings[i].string = language_strings[i].string.replace('>', "&gt");
            }
        }
        
        createForm();
        var language = '';
        language = getCookie('selected_language');
        if (language != '' && language != null) {
            hide_language_input();
        }
    
    });
    
    ajaxRequest.fail(function(jqXHR, textStatus, errorThrown) {
        
        var error = "<div class='error' > There has been an error retrieving the data.";
        error += "If this persists contact the site admin or <a href='mailto: b.ttalic@gmail.com?Subject=Translator%20Plugin%20Error'>b.ttalic</a></div>";
        $('#submission_form').append(error);
    
    });
}

function vote(sign, metadata) {
    console.log(sign + "  " + metadata.id);
}


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

function show_guidelines() {
    
    var translator = $('#translator');
    var r_url = get_image_url();
    var close_url = r_url + 'close.png';
    var highlight_url = r_url + 'highlight.png';
    var remove_highlight_url = r_url + 'rhighlight.png';
    var vote_up = r_url + 'vote_up.png';
    var vote_down = r_url + 'vote_down.png';
    
    var display = "<div id='translator_guidelines' style='height:" + translator.height() + "px; width:" + translator.width() + "px; top:" + translator.position().top + "px'> ";
    display += "<span > <img src= '" + close_url + "' onclick='hide_guidelines()' class='form_image'/>";
    display += "<div id='translator_guidelines_inner'>";
    display += "<ul>";
    display += "<li> Click <img src= '" + highlight_url + "'/> To highlight the string on the page. </li>";
    display += "<li> Highlight will not work if the string is inside title, value etc tags </li> ";
    display += "<li> Click <img src= '" + remove_highlight_url + "' /> To remove highlight from strings on the page. </li>";
    display += "<li> Click <img src= '" + vote_up + "' /> To vote up a translation you think is good. </li>";
    display += "<li> Click <img src= '" + vote_down + "' /> To vote down a translation you think is bad. </li>";
    display += "<li> If a string has &lttag&gt or &ltvar&gt in it writte them in an appropriate place in the translation </li>";
    
    display += "</ul>";
    display += "</div>";
    display += "</div>";
    $('#translator').append(display);

}

function hide_guidelines() {
    $('#translator_guidelines').remove();
}
