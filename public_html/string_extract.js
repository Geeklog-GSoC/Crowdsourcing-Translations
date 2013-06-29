/**
* persistant variable set
*/
var languages_name=[];
var languages_object=[];
var language_strings=new Array();

$(document).ready(function() {
  re_render_html();
  additional_purge();
  get_original_language_values();
  add_autocomplete_to_language_input();
  createForm();
});

/*
* retrieves the HTML of the current page
* removes all identifiers the plugin made for strings
* returns the clean html to the browser
*/
function re_render_html(){
  var html=$("html").html();

  while(html.indexOf('_-start_')>-1){

    var start_point=html.indexOf("_-start_")+8;

    var end_point=html.indexOf("_-end_");

    //extracting the original LANG string and the metadata
    var data=html.substring(start_point, end_point);
    var new_object=new Language_string(data)

    add_to_language_array(new_object);

    data="_-start_"+data+"_-end_";
    while(html.indexOf(data)>-1){
      html=remove_identificators(html, data, false, new_object);
    }
  }
  document.documentElement.innerHTML=html;
}


/* Creates a object from the data passed by the LANG arrays
 *@param data string the extracted data from the rendered page
 */
 function Language_string(data){
  this.array_name=extract_array_name(data);
  this.array_index=extract_array_index(data);
  this.string=extract_string(data);
  this.metadata=metadata(this.array_name,this.array_index);

  function extract_array_name(data){
    var start_point=data.indexOf("array__")+7;
    var end_point=data.indexOf("index__");
    var array_name=data.substring(start_point, end_point);  
    return array_name;
  };

  function extract_array_index(data) {
   var start_point=data.indexOf("index__")+7;
   var end_point=data.indexOf("||",data.indexOf("||")+2);
   var array_index=data.substring(start_point,end_point);
   return array_index;
 };

 function extract_string(data) {
   var start_point=data.indexOf("||",data.indexOf("||")+2)+2;
   var string=data.substring(start_point, data.length);
   return string;
 };

 function metadata(array_name, array_index) {
  var meta="array_"+array_name+"index_"+array_index;
  meta=meta.replace('$','');
  return meta;
}

this.equals=function equals(other_language_string){
  if( (this.array_name==other_language_string.array_name) && (this.array_index==other_language_string.array_index) && (this.string==other_language_string.string) )
    return true;
  else
    return false;
};

}


//Make sure every element is unique before adding it to the array
function add_to_language_array(element){
  for(var i=0; i<language_strings.length; i++){
    if(language_strings[i].equals(element)){
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

function remove_identificators(html, data, isFirst, new_object){
  //need the offset to make sure that the new <span> is not added to html tags such as <title>
  var offset=50;
  if(html.indexOf(data)<50){
    offset=html.indexOf(data);
  }
  var test_string=html.substring(html.indexOf(data)-offset,html.indexOf(data)+data.length);

  var isTag=true;
  var flags=["<title>", "value=", "title=", "alt=", "onclick="];
  for(var i=0; i<flags.length; i++){
    if(test_string.indexOf(flags[i])>0)
      isTag=false;
  }

  if(isFirst==true){

    if(isTag){
      html=html.replace("_-start_","<span class='"+new_object.metadata+"'>");
      html=html.replace("_-end_","</span>");
    } else {
      html=html.replace("_-start_","");
      html=html.replace("_-end_","");
    }

  } else {

    if(isTag)
      html=html.replace(data,"<span class='"+new_object.metadata+"'>"+new_object.string+"</span>");
    else
      html=html.replace(data,new_object.string);
  }

  return html;
}




/**
*In case there has been a oversee in removing identificators
* this function will take care of them
*/
function additional_purge(){
 for(var i=0; i<language_strings.length; i++){
  if(language_strings[i].string.indexOf("_-start_")>0){
    language_strings[i].string=language_strings[i].string.substring(0, language_strings[i].string.indexOf("_-start_"));
  }
}
}

/**
* Creates the <form> for the plugin bases on strings retrieved from the current page
* combined with markup created on installation of plugin
* finally all HTML and PHP variables will be dispalayed as <tag>
*/
function createForm(){

  var template="<label for='translator_input_COUNT'>STRING</label>";
  template +="<input id='translator_input_COUNT' name='translator_input_COUNT' placeholder='Click to higlight on page' onclick='highlight()' onblur='remove_highlight()'/>";
  template += "<input id='translator_input_COUNT_hidden' class='hidden' name='translator_input_COUNT_hidden' value='METADATA' />";

  if($('#translator_form').size()>0){
    var form=$('#translator_form');

    var length=language_strings.length;
    if(length>language_strings.length)
      length=language_strings.length;

    for(var count=0; count<length; count++){
      /* TODO
      Decide on number of input fields and selection algoritm
      */
      var i=count;//Math.floor(Math.random()*language_strings.length);

      var nex_input=template.replace(/COUNT/g, i );
      nex_input=nex_input.replace(/STRING/g, language_strings[i].string.replace(/<\/?([a-z][a-z0-9]*)\b[^>]*>?/gi, ''));

      nex_input=nex_input.replace(/METADATA/g,language_strings[i].metadata);

      form.append(nex_input);
    }
    form.append("<input type='submit' />");
  }
}

/**
* adds CSS class to highligh selected string(s) on page
*/
function highlight(){
  var id=event.target.id;
  var value=$('#'+id+"_hidden").val();

  var class_name='.'+value;
  $(class_name).each( function(){
    $(this).addClass('translator');
  });
}
/**
* removes CSS class of highlighted string(s) on page
*/
function remove_highlight(){
  $('.translator').each(function(){
    $(this).removeClass('translator');
  });
}

/*
* Gets list of available languages for translation via AJAX call
* and uses jQueryUI to create autocomplete option for the language selection input
*/
function add_autocomplete_to_language_input(){
  var r_url=window.location.pathname;
  r_url=r_url.substring(0,r_url.indexOf("public_html"));
  r_url+='plugins/crowdtranslator/get_languages.php'

  var ajaxRequest=$.ajax({
    url: r_url
  });

  ajaxRequest.done( function(response, textStatus, jqKHR) {
    languages_object = JSON.parse(response);

    for( var key in languages_object ){
      if(languages_object.hasOwnProperty(key)){
        languages_name.push(languages_object[key]);
      }
    }

    $("#translator_language").autocomplete({
      source: languages_name
    });
  });

  
}

function get_original_language_values(){
  var r_url=window.location.pathname;
  r_url=r_url.substring(0,r_url.indexOf("public_html"));
  r_url+='plugins/crowdtranslator/get_original_language_values.php'
  var json_ob=JSON.stringify(language_strings) ;

  var ajaxRequest=$.ajax({
    url: r_url,
    data: { objects: json_ob },
    type: "POST"
  });

  ajaxRequest.done( function(response, textStatus, jqKHR) {
   language_strings=JSON.parse(response);
   for(var i=0; i<language_strings.length; i++){
    while(language_strings[i]['string'].indexOf('<')>=0){
      language_strings[i].string=language_strings[i].string.replace('<', "&lt");
    }
    while(language_strings[i]['string'].indexOf('>')>=0){
      language_strings[i].string=language_strings[i].string.replace('>', "&gt");
    }
  }
});

  ajaxRequest.fail(function (jqXHR, textStatus, errorThrown){

    /*add fail handling code*/

  });
}