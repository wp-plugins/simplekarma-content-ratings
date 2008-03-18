/*
This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along
    with this program; if not, write to the Free Software Foundation, Inc.,
    51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/ 

function modifyKarma(objectId, value, actionString, remotePath, prefix, callBack)
{
    if(!callBack)
    {
        callBack = modifyKarmaCallBack;
    }
	// This is a work around a bug in firefox which allows the opposit image that was clicked to be clicked again once.
	var foo=document.getElementById('down-' + objectId).onclick;
	var bar=document.getElementById('up-' + objectId).onclick;
	
	if (simpleKarmaXmlHTTPRequest.readyState!=0 && simpleKarmaXmlHTTPRequest.readyState!=4)
		{
		
			return false;
		}

		remotePath = "http://" + remotePath + "rpc/process.php?";
		remotePath += "objectId=" + objectId;
		remotePath += "&value=" + value;
		remotePath += "&actionString=" + actionString;
		remotePath += "&prefix=" + prefix;
		simpleKarmaXmlHTTPRequest.open("GET", remotePath, true);
		simpleKarmaXmlHTTPRequest.onreadystatechange=callBack;
		simpleKarmaXmlHTTPRequest.send(null);
}

function modifyKarmaCallBack()
{
	if (simpleKarmaXmlHTTPRequest.readyState==4)
	{
	    var json = eval("(" + simpleKarmaXmlHTTPRequest.responseText + ")");
        document.getElementById("karma-" + json['id']).innerHTML=json['karma'];
		checkCookie(json['prefix'],json['id']);
		document.getElementById('down-' + json['id']).src=json['imgpathdown'];
		document.getElementById('up-' + json['id']).src=json['imgpathup'];
		document.getElementById('down-' + json['id']).onclick='';
		document.getElementById('up-' + json['id']).onclick='';
	}
}

function modifyKarmaCallBackAdmin()
{
	if (simpleKarmaXmlHTTPRequest.readyState==4)
	{
	    var json = eval("(" + simpleKarmaXmlHTTPRequest.responseText + ")");
        document.getElementById("karma-" + json['id']).innerHTML=json['karma'];
	}
}

function checkCookie(name, id)
{
	var cookiename=getCookie(name);
	if (cookiename!=null && cookiename!="")
	{
		setCookie("simple-karma-" + name, cookiename + '-' + id, 365);
	}
	else 
	{
		setCookie("simple-karma-" + name,id,365);
	}
}

function getCookie(name)
         {
         //Without this, it will return the first value 
         //in document.cookie when name is the empty string.
         if(name == '')
            return('');
         
         name_index = document.cookie.indexOf(name + '=');
         
         if(name_index == -1)
            return('');
         
         cookie_value =  document.cookie.substr(name_index + name.length + 1, 
                                                document.cookie.length);         
         
         //All cookie name-value pairs end with a semi-colon, except the last one.
         end_of_cookie = cookie_value.indexOf(';');
         if(end_of_cookie != -1)
            cookie_value = cookie_value.substr(0, end_of_cookie);

         //Restores all the blank spaces.
         space = cookie_value.indexOf('+');
         while(space != -1)
              { 
              cookie_value = cookie_value.substr(0, space) + ' ' + 
              cookie_value.substr(space + 1, cookie_value.length);
							 
              space = cookie_value.indexOf('+');
              }

         return(cookie_value);
         }

function setCookie(c_name,value,expiredays)
{


var exdate=new Date();
exdate.setDate(exdate.getDate()+expiredays);
document.cookie=c_name+ "=" +escape(value)+
((expiredays==null) ? "" : ";expires="+exdate.toGMTString());
}


function getHTTPObject()
{
var xmlHttp;
try
  {
  // Firefox, Opera 8.0+, Safari
  xmlHttp=new XMLHttpRequest();
  }
catch (e)
  {
  // Internet Explorer
  try
    {
    xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
    }
  catch (e)
    {
    try
      {
      xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
      }
    catch (e)
      {
      alert("Your browser does not support AJAX!");
      return false;
      }
    }
  }
  return xmlHttp;
  }

simpleKarmaXmlHTTPRequest = new getHTTPObject();
