$(function(){

   //defaults
   $.fn.editable.defaults.url = '/post'; 
 var c = window.location.href.match(/c=inline/i) ? 'inline' : 'popup';
            $.fn.editable.defaults.mode = c === 'inline' ? 'inline' : 'popup';
    
    $('[id^="dkim"]').editable({

        source: [
            {value: 'enabled', text: 'Enabled'},
            {value: 'not_enabled', text: 'Not Enabled'}
        ],
        display: function(value, sourceData) {
             var colors = {"": "gray", "enabled": "green", "not_enabled": "red"},
                 elem = $.grep(sourceData, function(o){return o.value == value;});
                 
             if(elem.length) {    
                 $(this).text(elem[0].text).css("color", colors[value]); 
             } else {
                 $(this).empty(); 
             }
        }   
    });    

    $('[id^="routing"]').editable({

        source: [
            {value: 'local', text: 'Local'},
            {value: 'external', text: 'External'}
        ],
        display: function(value, sourceData) {
             var colors = {"": "gray", "local": "green", "external": "red"},
                 elem = $.grep(sourceData, function(o){return o.value == value;});
                 
             if(elem.length) {    
                 $(this).text(elem[0].text).css("color", colors[value]); 
             } else {
                 $(this).empty(); 
             }
        }   
    });    

});
