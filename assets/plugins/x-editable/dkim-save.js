$(function(){
    //ajax mocks
    $.mockjaxSettings.responseTime = 500; 

    $.mockjax({
	url: '/post',
        response: function(settings) {


		  $.post("/emails/dkim/SaveDkim.php",
		  {
			dkim:settings.data['value'],
			domain_id:settings.data['pk']
		  },

		  function(data,status){
		    alert(data + "\n\nStatus: " + status);
		  });	
        }
    });

    $.mockjax({
        url: '/error',
        status: 400,
        statusText: 'Bad Request',
        response: function(settings) {
            this.responseText = 'Please input correct value'; 
            log(settings, this);
        }        
    });

    $.mockjax({
        url: '/status',
        status: 500,
        response: function(settings) {
            this.responseText = 'Internal Server Error';
            log(settings, this);
        }        
    });

    function log(settings, response) {

            var s = [], str;
            s.push(settings.type.toUpperCase() + ' url = "' + settings.url + '"');

            for(var a in settings.data) {
                if(settings.data[a] && typeof settings.data[a] === 'object') {
                    str = [];
                    for(var j in settings.data[a]) {str.push(j+': "'+settings.data[a][j]+'"');}
                    str = '{ '+str.join(', ')+' }';
                } else {
                    str = '"'+settings.data[a]+'"';
                }
                s.push(a + ' = ' + str);
            }

            s.push('RESPONSE: status = ' + response.status);

            if(response.responseText) {
                if($.isArray(response.responseText)) {
                    s.push('[');

                    $.each(response.responseText, function(i, v){
                       s.push('{value: ' + v.value+', text: "'+v.text+'"}');
                    }); 

                    s.push(']');
                } else {
                   s.push($.trim(response.responseText));
                }
            }

            s.push('--------------------------------------\n');
            $('#console').val(s.join('\n') + $('#console').val());
    }                 

});
