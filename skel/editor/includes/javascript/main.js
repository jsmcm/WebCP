var MasterData;
var ShortLinkTimeout = 500;


function Validate(URLObj, ShortCodeObj)
{

  URL = URLObj.value;
  ShortCode = ShortCodeObj.value;

	//alert("URL: " + URL);
	//alert("ShortCode: " + ShortCode);
	
  if( ShortCode.length < 3 ) 
  {
    alert("The short code must be at least 3 characters please...");
    ShortCodeObj.focus();
    return false;  
  }
  
  if( (URL.substring(0, 7) != "http://") && (URL.substring(0, 8) != "https://") )
  {
    
    URL = "http://" + URL;
    URLObj.value = URL;

    if( (URL.substring(0, 11) != "http://www.") )
    {
       alert("URL must contain either http:// or www, or both at the front (I've added it, click on 'Shorten!' again if its correct)");
       URLObj.focus();
       return false;
    }
  }

  if( (URL.indexOf(".") < 0) || (URL[URL.length - 1] == '.') )
  {
    alert("This URL is incorrect, please fix it first...");
    URLObj.focus();
    return false;
  }

  URL = escape(URL);
  ShortCode = escape(ShortCode);
  Shorten(URL, ShortCode);
  return true;
}



function ValidateRandom(URLObj)
{

  URL = URLObj.value;

  
  if( (URL.substring(0, 7) != "http://") && (URL.substring(0, 8) != "https://") )
  {
    
    URL = "http://" + URL;
    URLObj.value = URL;

    if( (URL.substring(0, 11) != "http://www.") )
    {
       alert("URL must contain either http:// or www, or both at the front (I've added it, click on 'Shorten!' again if its correct)");
       URLObj.focus();
       return false;
    }
  }

  if( (URL.indexOf(".") < 0) || (URL[URL.length - 1] == '.') )
  {
    alert("This URL is incorrect, please fix it first...");
    URLObj.focus();
    return false;
  }

  URL = escape(URL);
  Shorten(URL, "");
  return true;
}





function GetRandomShortLinks(AccountID)
{

	$(document).ready(function(){
	$.get('/includes/classes/RandomShortURLList.php?AccountID=' + AccountID, function(data) {PostShortURLList(data);} , "html");
     });	

     setTimeout('GetRandomShortLinks(' + AccountID + ')', ShortLinkTimeout);
}


function PostShortURLList(data)
{
	//alert(data);
	if(data != "")
	{
		
		if(MasterData != data)
		{
			ShortLinkTimeout = 500;
			MasterData = data;
			T = document.getElementById("RandomShortURLList")
			MakeDivVisible("RandomShortURLList", 0);
			T.innerHTML = data;
		}

		
		

	}

	
}



function Shorten(URL, ShortCode)
{

	$(document).ready(function(){
	$.get('/includes/classes/shorten.php?ShortCode=' + ShortCode + '&URL=' + URL, function(data) {PostShortURL(data);} , "html");
     });
     
	    
}

function PostShortURL(data)
{
	//alert(data);
	if(data != "")
	{
	
		T = document.getElementById("ShortenedURL")
		T.innerHTML = data;
		MakeDivVisible("xlekker",1);
		MakeDivVisible("ShortenedURL",1);

                ShortLinkTimeout = 100;
	        
		//$(document).ready(function(){
	        //$.get('mailer.php?ShortURL=' + data + '&URL=' + document.ShortenForm.URL.value);
                //});

	}
	else
	{
		MakeDivInvisible("xlekker");
		MakeDivInvisible("ShortenedURL");
		alert("That short code is not available, please try again...");
	}
	
	
}


function MakeDivInvisible(LinkName)
{

	if ( T = document.getElementById(LinkName) )
	{
		// exists
	}
	else
	{
		// does not exist
		return;
	}

	elem = document.getElementById(LinkName);

	elem.style.visibility = "hidden";
	elem.style.display = "none";

}


function MakeDivVisible(LinkName, DoInLine)
{

	if ( T = document.getElementById(LinkName) )
	{
		// exists
	}
	else
	{
		// does not exist
		return;
	}

	elem = document.getElementById(LinkName);

	elem.style.visibility = "visible";
	if(DoInLine == 1)
	{
		elem.style.display = "inline";
	}
}
