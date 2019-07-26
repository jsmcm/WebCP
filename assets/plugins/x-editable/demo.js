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

    $('[id^="wwwredirect"]').editable({

        source: [
            {value: 'none', text: 'No Redirect'},
            {value: 'www', text: 'Redirect to WWW'},
            {value: 'naked', text: 'Redirect to Naked Domain'}
        ],
        display: function(value, sourceData) {
             var colors = {"": "gray", "none": "red", "www": "green", "naked": "green"},
                 elem = $.grep(sourceData, function(o){return o.value == value;});
                 
             if(elem.length) {    
                 $(this).text(elem[0].text).css("color", colors[value]); 
             } else {
                 $(this).empty(); 
             }
        }   
    });   


    
    $('[id^="ssh_key_authorise_"]').editable({
        params: function(params) {
            params.nonce_timestamp = $(this).editable().data('nonce-timestamp');
            params.nonce_value = $(this).editable().data('nonce-value');
            params.domain_id = $(this).editable().data('domain-id');
            
            return params;
    
        },
        source: [
            {value: '0', text: 'Not authorised'},
            {value: '1', text: 'Authorised'}
        ],
        display: function(value, sourceData) {

             var colors = {"": "gray", "0": "red", "1": "green"},
                 elem = $.grep(sourceData, function(o){return o.value == value;});
                 
             if(elem.length) {    
                 $(this).text(elem[0].text).css("color", colors[value]); 
             } else {
                 $(this).empty(); 
             }
        }   
    });   


    $('[id^="ssl_redirect_"]').editable({

        source: [
            {value: 'none', text: 'No Redirect'},
            {value: 'enforce', text: 'Redirect to HTTPS'},
        ],
        display: function(value, sourceData) {
             var colors = {"": "gray", "none": "red", "enforce": "green"},
                 elem = $.grep(sourceData, function(o){return o.value == value;});
                 
             if(elem.length) {    
                 $(this).text(elem[0].text).css("color", colors[value]); 
             } else {
                 $(this).empty(); 
             }
        }   
    });   


    $('[id^="parked_redirect_"]').editable({

        source: [
            {value: 'none', text: 'No Redirect'},
            {value: 'redirect', text: 'Redirect to Parent'},
        ],
        display: function(value, sourceData) {
             var colors = {"": "gray", "none": "red", "redirect": "green"},
                 elem = $.grep(sourceData, function(o){return o.value == value;});
                 
             if(elem.length) {    
                 $(this).text(elem[0].text).css("color", colors[value]); 
             } else {
                 $(this).empty(); 
             }
        }   
    });   



    $('[id^="domain_transactional_email_"]').editable({

        source: [
            {value: 'none', text: 'None'},
            {value: 'sendgrid', text: 'Using Sendgrid'},
        ],
        display: function(value, sourceData) {
             var colors = {"": "gray", "none": "red", "sendgrid": "green"},
                 elem = $.grep(sourceData, function(o){return o.value == value;});
                 
             if(elem.length) {    
                 $(this).text(elem[0].text).css("color", colors[value]); 
             } else {
                 $(this).empty(); 
             }
        }   
    });   


});
