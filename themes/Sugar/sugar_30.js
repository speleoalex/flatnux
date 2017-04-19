/**
 * Javascript file for Sugar
 *
 * The contents of this file are subject to the SugarCRM Public License Version
 * 1.1.3 ("License"); You may not use this file except in compliance with the
 * License. You may obtain a copy of the License at http://www.sugarcrm.com/SPL
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied.  See the License
 * for the specific language governing rights and limitations under the
 * License.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *    (i) the "Powered by SugarCRM" logo and
 *    (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * The Original Code is: SugarCRM Open Source
 * The Initial Developer of the Original Code is SugarCRM, Inc.
 * Portions created by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.;
 * All Rights Reserved.
 * Contributor(s): ______________________________________.
 */

// $Id: sugar_3.js,v 1.47.2.4 2006/05/12 00:38:29 wayne Exp $

/**
 * DHTML date validation script. Courtesy of SmartWebby.com (http://www.smartwebby.com/dhtml/)
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 */
// Declaring valid date character, minimum year and maximum year
var dtCh= "-";
var minYear=1900;
var maxYear=2100;
var nameIndex = 0;
var typeIndex = 1;
var requiredIndex = 2;
var msgIndex = 3;
var jstypeIndex = 5;
var minIndex = 10;
var maxIndex = 11;
var compareToIndex = 7;
var allowblank = 8;
var validate = new Array();
var maxHours = 24;
var requiredTxt = 'Missing Required Field:'
var invalidTxt = 'Invalid Value:'
var secondsSinceLoad = 0;
var inputsWithErrors = new Array();
var lastSubmitTime = 0;
var alertList = new Array();
var oldStartsWith = '';

function isSupportedIE() {
	var userAgent = navigator.userAgent.toLowerCase() ;

	// IE Check supports ActiveX controls
	if (userAgent.indexOf("msie") != -1 && userAgent.indexOf("mac") == -1 && userAgent.indexOf("opera") == -1) {
		var version = navigator.appVersion.match(/MSIE (.\..)/)[1] ;
		if(version >= 5.5 ) {
			return true;
		} else {
			return false;
		}
	}
}
var isIE = isSupportedIE();

// escapes regular expression characters
RegExp.escape = function(text) { // http://simon.incutio.com/archive/2006/01/20/escape
  if (!arguments.callee.sRE) {
    var specials = ['/', '.', '*', '+', '?', '|','(', ')', '[', ']', '{', '}', '\\'];
    arguments.callee.sRE = new RegExp('(\\' + specials.join('|\\') + ')', 'g');
  }
  return text.replace(arguments.callee.sRE, '\\$1');
}

function addAlert(type, name,subtitle, description,time, redirect) {
	var addIndex = alertList.length;
	alertList[addIndex]= new Array();
	alertList[addIndex]['name'] = name;
	alertList[addIndex]['type'] = type;
	alertList[addIndex]['subtitle'] = subtitle;
	alertList[addIndex]['description'] = description.replace(/<br>/gi, "\n").replace(/&amp;/gi,'&').replace(/&lt;/gi,'<').replace(/&gt;/gi,'>').replace(/&#039;/gi,'\'').replace(/&quot;/gi,'"');
	alertList[addIndex]['time'] = time;
	alertList[addIndex]['done'] = 0;
	alertList[addIndex]['redirect'] = redirect;
} 
function checkAlerts() {
	secondsSinceLoad += 1;
	var mj = 0;
	var alertmsg = '';
	for(mj = 0 ; mj < alertList.length; mj++) {
		if(alertList[mj]['done'] == 0) {
			if(alertList[mj]['time'] < secondsSinceLoad && alertList[mj]['time'] > -1 ) {
				alertmsg = alertList[mj]['type'] + ":" + alertList[mj]['name'] + "\n" +alertList[mj]['subtitle']+ "\n"+ alertList[mj]['description'] + "\n\n";
				alertList[mj]['done'] = 1;
				if(alertList[mj]['redirect'] == '') {
					alert(alertmsg);
				} 
				else if(confirm(alertmsg)) {
					window.location = alertList[mj]['redirect'];
				}
			}
		}
	}
	
	setTimeout("checkAlerts()", 1000);
}

function toggleDisplay(id) {		
	if(this.document.getElementById(id).style.display == 'none') {
		this.document.getElementById(id).style.display = '';
		if(this.document.getElementById(id+"link") != undefined) {
			this.document.getElementById(id+"link").style.display = 'none';
		}
	}
	else {
		this.document.getElementById(id).style.display = 'none'
		if(this.document.getElementById(id+"link") != undefined) {
			this.document.getElementById(id+"link").style.display = '';
		}
	}
}

function checkAll(form, field, value) {
	for (i = 0; i < form.elements.length; i++) {
		if(form.elements[i].name == field)
			form.elements[i].checked = value;
	}
}

function replaceAll(text, src, rep) {
	offset = text.toLowerCase().indexOf(src.toLowerCase());
	while(offset != -1) {
		text = text.substring(0, offset) + rep + text.substring(offset + src.length ,text.length);
		offset = text.indexOf( src, offset + rep.length + 1);
	}
	return text;
}

function addForm(formname) {
	validate[formname] = new Array();
}

function addToValidate(formname, name, type,required, msg) {
	if(typeof validate[formname] == 'undefined') {
		addForm(formname);
	}
	validate[formname][validate[formname].length] = new Array(name, type,required, msg);	
}

function addToValidateRange(formname, name, type,required,  msg,min,max) {
	addToValidate(formname, name, type,required,  msg);
	validate[formname][validate[formname].length - 1][jstypeIndex] = 'range'
	validate[formname][validate[formname].length - 1][minIndex] = min;
	validate[formname][validate[formname].length - 1][maxIndex] = max;
}

function addToValidateDateBefore(formname, name, type, required, msg, compareTo) {
	addToValidate(formname, name, type,required,  msg);
	validate[formname][validate[formname].length - 1][jstypeIndex] = 'isbefore'
	validate[formname][validate[formname].length - 1][compareToIndex] = compareTo;
}

function addToValidateDateBeforeAllowBlank(formname, name, type, required, msg, compareTo, allowBlank) {
	addToValidate(formname, name, type,required,  msg);
	validate[formname][validate[formname].length - 1][jstypeIndex] = 'isbefore'
	validate[formname][validate[formname].length - 1][compareToIndex] = compareTo;
	validate[formname][validate[formname].length - 1][allowblank] = allowBlank;
}

function addToValidateBinaryDependency(formname, name, type, required, msg, compareTo) {
	addToValidate(formname, name, type, required, msg);
	validate[formname][validate[formname].length - 1][jstypeIndex] = 'binarydep';
	validate[formname][validate[formname].length - 1][compareToIndex] = compareTo;
}

function removeFromValidate(formname, name) {
	for(i = 0; i < validate[formname].length; i++){
		if(validate[formname][i][nameIndex] == name){
			validate[formname].splice(i, 1);
		}
	}
}

function toDecimal(original) {
	temp = Math.round(original*100)/100;
	if((original * 100) % 100 == 0)
		return temp + '.00';
	if((original * 10) % 10 == 0)
		return temp + '0';
	return temp
}

function isInteger(s) {
	if(typeof num_grp_sep != 'undefined' && typeof dec_sep != 'undefined')
		s = unformatNumber(s, num_grp_sep, dec_sep).toString();

	var i;
    for (i = 0; i < s.length; i++){
        // Check that current character is number.
        var c = s.charAt(i);
        if (((c < "0") || (c > "9"))) return false;
    }
    // All characters are numbers.
    return true;
}

function isNumeric(s) {
  if(!/^-*[0-9\.]+$/.test(s)) {
   		return false
   }
   else {
		return true;
   }
}

function stripCharsInBag(s, bag) {
	var i;
    var returnString = "";
    // Search through string's characters one by one.
    // If character is not in bag, append to returnString.
    for (i = 0; i < s.length; i++){
        var c = s.charAt(i);
        if (bag.indexOf(c) == -1) returnString += c;
    }
    return returnString;
}

function daysInFebruary(year) {
	// February has 29 days in any year evenly divisible by four,
    // EXCEPT for centurial years which are not also divisible by 400.
    return (((year % 4 == 0) && ( (!(year % 100 == 0)) || (year % 400 == 0))) ? 29 : 28 );
}

function DaysArray(n) {
	for (var i = 1; i <= n; i++) {
		this[i] = 31
		if (i==4 || i==6 || i==9 || i==11) {this[i] = 30}
		if (i==2) {this[i] = 29}
   }
   return this
}

var date_reg_positions = {'Y': 1,'m': 2,'d': 3};
var date_reg_format = '([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})'
function isDate(dtStr) {
	if(dtStr.length== 0) {
		return true;
	}
	myregexp = new RegExp(date_reg_format)
	if(!myregexp.test(dtStr))
		return false

return true
}

function getDateObject(dtStr) {
	if(dtStr.length== 0) {
		return true;
	}

	myregexp = new RegExp(date_reg_format)

	if(myregexp.exec(dtStr)) var dt = myregexp.exec(dtStr)
	else return false;

	var yr = dt[date_reg_positions['Y']];
	var mh = dt[date_reg_positions['m']];
	var dy = dt[date_reg_positions['d']];
	var date1 = new Date();
	date1.setFullYear(yr); // xxxx 4 char year
	date1.setMonth(mh-1); // 0-11 Bug 4048: javascript Date obj months are 0-index
	date1.setDate(dy); // 1-31
	return date1;
}

function isBefore(value1, value2) {
	var d1 = getDateObject(value1);
	var d2 = getDateObject(value2);
	
	return d2 >= d1;
}

function isValidEmail(emailStr) {
	if(emailStr.length== 0) {
		return true;
	}
	if(!/^\w+(['\.\-\+]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,4})+$/.test(emailStr))
		return false
	return true	
}

function isValidPhone(phoneStr) {
	if(phoneStr.length== 0) {
		return true;
	}
	if(!/^[0-9\-\(\)]+$/.test(phoneStr))
		return false
	return true	
}
function isFloat(floatStr) {
	floatStr = unformatNumber(floatStr, num_grp_sep, dec_sep).toString();
	if(floatStr.length== 0) {
		return true;
	}
	if(!/^[0-9\.]+$/.test(floatStr))
		return false
	return true	
}
var time_reg_format = "[0-9]{1,2}\:[0-9]{2}";
function isTime(timeStr) {
	time_reg_format = time_reg_format.replace('([ap]m)', '');
	time_reg_format = time_reg_format.replace('([AP]M)', '');
	if(timeStr.length== 0){
		return true;
	}
	//we now support multiple time formats
	myregexp = new RegExp(time_reg_format)
	if(!myregexp.test(timeStr))
		return false

	return true
}

function inRange(value, min, max) {
	return value >= min && value <= max;
}

function bothExist(item1, item2) {
	if(typeof item1 == 'undefined') { return false; }
	if(typeof item2 == 'undefined') { return false; }
	if((item1 == '' && item2 != '') || (item1 != '' && item2 == '') ) { return false; }
	return true;
}

function trim(s) {
	if(typeof(s) == 'undefined')  
		return s;
	while (s.substring(0,1) == " ") {
		s = s.substring(1, s.length);
	}
	while (s.substring(s.length-1, s.length) == ' ') {
		s = s.substring(0,s.length-1);
	}

	return s;
}


function check_form(formname) {
	if (typeof(siw) != 'undefined' && siw 
		&& typeof(siw.selectingSomething) != 'undefined' && siw.selectingSomething)
			return false;
	return validate_form(formname, '');	
}

function add_error_style(formname, input, txt) {
	inputHandle = eval("document." + formname + "['" + input + "']");
	style = get_current_bgcolor(inputHandle);
		
	if(inputHandle.parentNode.innerHTML.search(txt) == -1) {
		errorTextNode = document.createElement('span');
		errorTextNode.className = 'required';
		errorTextNode.innerHTML = '<br />' + txt;
		inputHandle.parentNode.appendChild(errorTextNode);		
	}

	inputHandle.style.backgroundColor = "#FF0000";
	inputsWithErrors.push(inputHandle);

	for(wp = 1; wp <= 10; wp++) {
		window.setTimeout('fade_error_style(style, ' + wp * 10 + ')', 1000 + (wp * 50));
	}
}

function get_current_bgcolor(input) {
	if(input.currentStyle) {// ie 
		style = input.currentStyle.backgroundColor;
		return style.substring(1,7);
	}
	else {// moz
		style = '';
		styleRGB = document.defaultView.getComputedStyle(input, '').getPropertyValue("background-color");
		comma = styleRGB.indexOf(',');
		style += dec2hex(styleRGB.substring(4, comma));
		commaPrevious = comma;
		comma = styleRGB.indexOf(',', commaPrevious+1);
		style += dec2hex(styleRGB.substring(commaPrevious+2, comma));
		style += dec2hex(styleRGB.substring(comma+2, styleRGB.lastIndexOf(')')));
		return style;
	}
}

function hex2dec(hex){return(parseInt(hex,16));}
var hexDigit=new Array("0","1","2","3","4","5","6","7","8","9","A","B","C","D","E","F");
function dec2hex(dec){return(hexDigit[dec>>4]+hexDigit[dec&15]);}

function fade_error_style(normalStyle, percent) {	
	errorStyle = 'FF0000';
	var r1 = hex2dec(errorStyle.slice(0,2));
	var g1 = hex2dec(errorStyle.slice(2,4));
	var b1 = hex2dec(errorStyle.slice(4,6));
	
	var r2 = hex2dec(normalStyle.slice(0,2));
	var g2 = hex2dec(normalStyle.slice(2,4));
	var b2 = hex2dec(normalStyle.slice(4,6));


	var pc = percent / 100;

	r= Math.floor(r1+(pc*(r2-r1)) + .5);
	g= Math.floor(g1+(pc*(g2-g1)) + .5);
	b= Math.floor(b1+(pc*(b2-b1)) + .5);

	for(var wp = 0; wp < inputsWithErrors.length; wp++) {
		inputsWithErrors[wp].style.backgroundColor = "#" + dec2hex(r) + dec2hex(g) + dec2hex(b);
	}
}


function validate_form(formname, startsWith){
	if ( typeof (formname) == 'undefined')
	{
		return false;
	}
	if ( typeof (validate[formname]) == 'undefined')
	{
		return true;
	}
	var form = "document." + formname;
	var isError = false;
	var errorMsg = "";
	
	var _date = new Date();
	if(_date.getTime() < (lastSubmitTime + 2000) && startsWith == oldStartsWith) { // ignore submits for the next 2 seconds
		return false;
	}
	lastSubmitTime = _date.getTime();
	oldStartsWith = startsWith;
	
	for(var wp = 0; wp < inputsWithErrors.length; wp++) {
		inputsWithErrors[wp].parentNode.removeChild(inputsWithErrors[wp].parentNode.lastChild);
	} // remove previous error messages
	
	inputsWithErrors = new Array();
	for(var i = 0; i < validate[formname].length; i++){
			if(validate[formname][i][nameIndex].indexOf(startsWith) == 0){
				if(typeof eval(form + "['" + validate[formname][i][nameIndex] + "']" ) != 'undefined'){
					var bail = false;
					if(validate[formname][i][requiredIndex]){
						if(typeof eval(form + "['" + validate[formname][i][nameIndex] + "']") == 'undefined' || trim(eval(form + "['" + validate[formname][i][nameIndex] + "']" + ".value")) == ""){
							add_error_style(formname, validate[formname][i][nameIndex], requiredTxt +' ' + validate[formname][i][msgIndex]);
							isError = true;
						}
					}
					if(!bail){
						switch(validate[formname][i][typeIndex]){
						case 'email':
							if(!isValidEmail(trim(eval(form + "['" + validate[formname][i][nameIndex] + "']" + ".value")))){
								isError = true;
								add_error_style(formname, validate[formname][i][nameIndex], invalidTxt + " " +	validate[formname][i][msgIndex]);
							}
							 break;
						case 'time':
							if( !isTime(trim(eval(form+"['" + validate[formname][i][nameIndex] + "']" + ".value")))){
								isError = true;
								add_error_style(formname, validate[formname][i][nameIndex], invalidTxt + " " +	validate[formname][i][msgIndex]);
							} break;
						case 'date': if(!isDate(trim(eval(form + "['" + validate[formname][i][nameIndex] + "']" + ".value")))){
								isError = true;
								add_error_style(formname, validate[formname][i][nameIndex], invalidTxt + " " +	validate[formname][i][msgIndex]);
							}  break;
						case 'alpha': 
							break;
						case 'alphanumeric': 
							break;
						case 'int':
							if(!isInteger(trim(eval(form + "['" + validate[formname][i][nameIndex] + "']" + ".value")))){
								isError = true;
								add_error_style(formname, validate[formname][i][nameIndex], invalidTxt + " " +	validate[formname][i][msgIndex]);
							}
							break;
						case 'float':
							if(!isFloat(trim(eval(form + "['" + validate[formname][i][nameIndex] + "']" + ".value")))){
								isError = true;
								add_error_style(formname, validate[formname][i][nameIndex], invalidTxt + " " +	validate[formname][i][msgIndex]);
							} 
							break;
						}

						if(typeof validate[formname][i][jstypeIndex]  != 'undefined'/* && !isError*/){

							switch(validate[formname][i][jstypeIndex]){
							case 'range':
								if(!inRange(trim(eval(form + "['" + validate[formname][i][nameIndex] + "']" + ".value")), validate[formname][i][minIndex], validate[formname][i][maxIndex])){
									isError = true;
									add_error_style(formname, validate[formname][i][nameIndex], validate[formname][i][msgIndex] + " value " + eval(form + "['" + validate[formname][i][nameIndex] + "']" + ".value") + " is not within the valid range (" +validate[formname][i][minIndex] + " - " + validate[formname][i][maxIndex] +  ") ");
								}
							break;
							case 'isbefore':
								compareTo = form + "." + validate[formname][i][compareToIndex];
								if(	typeof compareTo != 'undefined'){
									if( trim(eval(compareTo + '.value')) == '' && (validate[formname][i][allowblank] == 'true') ) {
										date2 = '2200-01-01';
									} else {
										date2 = trim(eval(compareTo + '.value'));
									}
																		
									date1 = trim(eval(form + "['" + validate[formname][i][nameIndex] + "']" + ".value"));

									if(trim(date1).length != 0 && !isBefore(date1,date2)){ 		
										isError = true;
										add_error_style(formname, validate[formname][i][nameIndex], validate[formname][i][msgIndex] + "(" + date1 + ") is not before " + date2);
									}
								}
							break;
							case 'binarydep':
								compareTo = form + "." + validate[formname][i][compareToIndex];
								if( typeof compareTo != 'undefined') {
									item1 = trim(eval(form + "['" + validate[formname][i][nameIndex] + "']" + ".value"));
									item2 = trim(eval(compareTo + '.value'));
									if(!bothExist(item1, item2)) {
										isError = true;
										add_error_style(formname, validate[formname][i][nameIndex], validate[formname][i][msgIndex]);
									}
								}
							break;
							}
						}
					}
				}
			}
		}
	if (isError == true) {
		var nw, ne, sw, se;
		if (self.pageYOffset) // all except Explorer
		{
			nwX = self.pageXOffset;
			seX = self.innerWidth;
			nwY = self.pageYOffset;
			seY = self.innerHeight;
		}
		else if (document.documentElement && document.documentElement.scrollTop) // Explorer 6 Strict
		{
			nwX = document.documentElement.scrollLeft;
			seX = document.documentElement.clientWidth;
			nwY = document.documentElement.scrollTop; 
			seY = document.documentElement.clientHeight;
		}
		else if (document.body) // all other Explorers
		{
			nwX = document.body.scrollLeft;
			seX = document.body.clientWidth;
			nwY = document.body.scrollTop;
			seY = document.body.clientHeight;
		}

		var inView = true; // is there an error within viewport of browser
		for(var wp = 0; wp < inputsWithErrors.length; wp++) {
			var elementCoor = findElementPos(inputsWithErrors[wp]); 
			if(!(elementCoor.x >= nwX && elementCoor.y >= nwY &&
				elementCoor.x <= seX && elementCoor.y <= seY)) { // if input is not within viewport
					inView = false;
					scrollToTop = elementCoor.y - 75;
					scrollToLeft = elementCoor.x - 75;
			}
			else { // on first input within viewport, don't scroll
				break;
			}
		}
		
		if(!inView) window.scrollTo(scrollToTop, scrollToLeft);

		return false;
	}
	
	return true;
	
}


/**
 * This array is used to remember mark status of rows in browse mode
 */
var marked_row = new Array;


/**
 * Sets/unsets the pointer and marker in browse mode
 *
 * @param   object    the table row
 * @param   interger  the row number
 * @param   string    the action calling this script (over, out or click)
 * @param   string    the default background color
 * @param   string    the color to use for mouseover
 * @param   string    the color to use for marking a row
 *
 * @return  boolean  whether pointer is set or not
 */
function setPointer(theRow, theRowNum, theAction, theDefaultColor, thePointerColor, theMarkColor) {
    var theCells = null;

    // 1. Pointer and mark feature are disabled or the browser can't get the
    //    row -> exits
    if ((thePointerColor == '' && theMarkColor == '')
        || typeof(theRow.style) == 'undefined') {
        return false;
    }

    // 2. Gets the current row and exits if the browser can't get it
    if (typeof(document.getElementsByTagName) != 'undefined') {
        theCells = theRow.getElementsByTagName('td');
    }
    else if (typeof(theRow.cells) != 'undefined') {
        theCells = theRow.cells;
    }
    else {
        return false;
    }

    // 3. Gets the current color...
    var rowCellsCnt  = theCells.length;
    var domDetect    = null;
    var currentColor = null;
    var newColor     = null;
    // 3.1 ... with DOM compatible browsers except Opera that does not return
    //         valid values with "getAttribute"
    if (typeof(window.opera) == 'undefined'
        && typeof(theCells[0].getAttribute) != 'undefined') {
        currentColor = theCells[0].getAttribute('bgcolor');
        domDetect    = true;
    }
    // 3.2 ... with other browsers
    else {
        currentColor = theCells[0].style.backgroundColor;
        domDetect    = false;
    } // end 3

    // 4. Defines the new color
    // 4.1 Current color is the default one
    if (currentColor == ''
        || currentColor.toLowerCase() == theDefaultColor.toLowerCase()) {
        if (theAction == 'over' && thePointerColor != '') {
            newColor              = thePointerColor;
        }
        else if (theAction == 'click' && theMarkColor != '') {
            newColor              = theMarkColor;
            marked_row[theRowNum] = true;
        }
    }
    // 4.1.2 Current color is the pointer one
    else if (currentColor.toLowerCase() == thePointerColor.toLowerCase()
             && (typeof(marked_row[theRowNum]) == 'undefined' || !marked_row[theRowNum])) {
        if (theAction == 'out') {
            newColor              = theDefaultColor;
        }
        else if (theAction == 'click' && theMarkColor != '') {
            newColor              = theMarkColor;
            marked_row[theRowNum] = true;
        }
    }
    // 4.1.3 Current color is the marker one
    else if (currentColor.toLowerCase() == theMarkColor.toLowerCase()) {
        if (theAction == 'click') {
            newColor              = (thePointerColor != '')
                                  ? thePointerColor
                                  : theDefaultColor;
            marked_row[theRowNum] = (typeof(marked_row[theRowNum]) == 'undefined' || !marked_row[theRowNum])
                                  ? true
                                  : null;
        }
    } // end 4

    // 5. Sets the new color...
    if (newColor) {
        var c = null;
        // 5.1 ... with DOM compatible browsers except Opera
        if (domDetect) {
            for (c = 0; c < rowCellsCnt; c++) {
                theCells[c].setAttribute('bgcolor', newColor, 0);
            } // end for
        }
        // 5.2 ... with other browsers
        else {
            for (c = 0; c < rowCellsCnt; c++) {
                theCells[c].style.backgroundColor = newColor;
            }
        }
    } // end 5

    return true;
} // end of the 'setPointer()' function


/**
  * listbox redirection
  */
function goToUrl(selObj, goToLocation) {
    eval("document.location.href = '" + goToLocation + "pos=" + selObj.options[selObj.selectedIndex].value + "'");
}



var json_objects = new Object();

function getXMLHTTPinstance() {
	var xmlhttp = false;
	var userAgent = navigator.userAgent.toLowerCase() ;

	// IE Check supports ActiveX controls
	if (userAgent.indexOf("msie") != -1 && userAgent.indexOf("mac") == -1 && userAgent.indexOf("opera") == -1) {
		var version = navigator.appVersion.match(/MSIE (.\..)/)[1] ;
		if(version >= 5.5 ) {
			try {
				xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
			}
			catch (e) {
				try {
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}
				catch (E) {
					xmlhttp = false;
				}
			}
		}
	}
	
	if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
		xmlhttp = new XMLHttpRequest();
	}
	return xmlhttp;
}

// NOW LOAD THE OBJECT..
var global_xmlhttp = getXMLHTTPinstance();

function http_fetch_sync(url,post_data) {
	global_xmlhttp = getXMLHTTPinstance();
	var method = 'GET';

	if(typeof(post_data) != 'undefined') method = 'POST';
	try {
		global_xmlhttp.open(method, url,false);
	}
	catch(e) {
		alert('message:'+e.message+":url:"+url);
	}
	if(method == 'POST') {
		global_xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	}

	global_xmlhttp.send(post_data);
	
	var args = {"responseText" : global_xmlhttp.responseText,
				"responseXML" : global_xmlhttp.responseXML,
				"request_id" : request_id};
	return args;

}
// this is a GET unless post_data is defined

function http_fetch_async(url,callback,request_id,post_data) {
	var method = 'GET';
	if(typeof(post_data) != 'undefined') {
		method = 'POST';
	}
	
	try {
		global_xmlhttp.open(method, url,true);
	}
	catch(e) {
		alert('message:'+e.message+":url:"+url);
	}
	if(method == 'POST') {
		global_xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	}
	global_xmlhttp.onreadystatechange = function() {                                                
		if(global_xmlhttp.readyState==4) {
			if(global_xmlhttp.status == 200) {
				var args = {"responseText" : global_xmlhttp.responseText,
							"responseXML" : global_xmlhttp.responseXML,
							"request_id" : request_id };
				callback.call(document,args);
			}
			else {
				alert("There was a problem retrieving the XML data:\n" + global_xmlhttp.statusText);
			}
		}
	}
	global_xmlhttp.send(post_data);
}

function call_json_method(module,action,vars,variable_name,callback) {
	global_xmlhttp.open("GET", "json.php?module="+module+"&action="+action+"&"+vars,true);
	global_xmlhttp.onreadystatechange=
	function() {
		if(global_xmlhttp.readyState==4) {
			if(global_xmlhttp.status == 200) {
				try {
					eval("json_objects['"+variable_name+"'] =" + global_xmlhttp.responseText);
			    } 
			    catch(exception) {
			         alert("ERROR:"+exception+", returned from server:"+global_xmlhttp.responseText);
				}
				var args = {responseText:global_xmlhttp.responseText,responseXML:global_xmlhttp.responseXML};
				callback.call(document,args);
			} 
			else {
				alert("There was a problem retrieving the XML data:\n" +
				global_xmlhttp.statusText);
			}
		}
	}
	global_xmlhttp.send(null);
}

function insert_at_cursor(field, value) {
 //ie:
	if (document.selection) {
		field.focus();
		sel = document.selection.createRange();
		sel.text = value;
	}
 //mozilla:
	else if(field.selectionStart || field.selectionStart == '0') {
		var start_pos = field.selectionStart;
		var end_pos = field.selectionEnd;
		field.value = field.value.substring(0, start_pos) + value + field.value.substring(end_pos, field.value.length);
	} 
	else {
		field.value += value;
	}
}

function checkParentType(type,button) {
	if(button == null) {
		return;
	}
	if(typeof(disabledModules[type]) != 'undefined') {
		button.disabled='disabled';
	}
	else {
		button.disabled = false;
	}
}

function parseDate(input, format) {
	date = input.value;
	format = format.replace(/%/g, '');
	sep = format.charAt(1);
	yAt = format.indexOf('Y')
	// 1-1-06 or 1-12-06 or 1-1-2006 or 1-12-2006
	if(date.match(/^\d{1,2}[\/-]\d{1,2}[\/-]\d{2,4}$/) && yAt == 4) {
		if(date.match(/^\d{1}[\/-].*$/)) date = '0' + date;
		if(date.match(/^\d{2}[\/-]\d{1}[\/-].*$/)) date = date.substring(0,3) + '0' + date.substring(3,date.length);
		if(date.match(/^\d{2}[\/-]\d{2}[\/-]\d{2}$/)) date = date.substring(0,6) + '20' + date.substring(6,date.length);
	}
	// 06-11-1 or 06-1-1
	else if(date.match(/^\d{2,4}[\/-]\d{1,2}[\/-]\d{1,2}$/)) {
		if(date.match(/^\d{2}[\/-].*$/)) date = '20' + date;
		if(date.match(/^\d{4}[\/-]\d{1}[\/-].*$/)) date = date.substring(0,5) + '0' + date.substring(5,date.length);		
		if(date.match(/^\d{4}[\/-]\d{2}[\/-]\d{1}$/)) date = date.substring(0,8) + '0' + date.substring(8,date.length);		
	}
	else if(date.match(/^\d{4,8}$/)) { // digits only
		digits = 0;
		if(date.match(/^\d{8}$/)) digits = 8;// match for 8 digits
		else if(date.match(/\d{6}/)) digits = 6;// match for 5 digits
		else if(date.match(/\d{4}/)) digits = 4;// match for 5 digits
		else if(date.match(/\d{5}/)) digits = 5;// match for 5 digits
		
		switch(yAt) {
			case 0:
				switch(digits) {
					case 4: date = '20' + date.substring(0,2) + sep + '0' + date.substring(2, 3) + sep + '0' + date.substring(3,4); break;
					case 5: date = '20' + date.substring(0,2) + sep + date.substring(2, 4) + sep + '0' + date.substring(4,5); break;
					case 6: date = '20' + date.substring(0,2) + sep + date.substring(2, 4) + sep + date.substring(4,6); break;				
					case 8: date = date.substring(0,4) + sep + date.substring(4, 6) + sep + date.substring(6,8); break;
				}
				break;
			case 2:
				switch(digits) {
					case 4: date = '0' + date.substring(0,1) + sep + '20' + date.substring(1, 3) + sep + '0' + date.substring(3,4); break;
					case 5: date = date.substring(0,2) + sep + '20' + date.substring(2, 4) + sep + '0' + date.substring(4,5); break;
					case 6: date = date.substring(0,2) + sep + '20' + date.substring(2, 4) + sep + date.substring(4,6); break;				
					case 8: date = date.substring(0,2) + sep + date.substring(2, 6) + sep + date.substring(6,8); break;
				}
			case 4:
				switch(digits) {
					case 4: date = '0' + date.substring(0,1) + sep + '0' + date.substring(1, 2) + sep + '20' + date.substring(2,4); break;
					case 5: date = '0' + date.substring(0,1) + sep + date.substring(1, 3) + sep + '20' + date.substring(3,5); break;
					case 6: date = date.substring(0,2) + sep + date.substring(2, 4) + sep + '20' + date.substring(4,6); break;				
					case 8: date = date.substring(0,2) + sep + date.substring(2, 4) + sep + date.substring(4,8); break;
				}
				break;
		}
	}
	date = date.replace(/[\/-]/g, sep);
	input.value = date;
}

// find obj's position
function findElementPos(obj) {
    var x = 0;
    var y = 0;
    if (obj.offsetParent) {
      while (obj.offsetParent) {
        x += obj.offsetLeft;
        y += obj.offsetTop;
        obj = obj.offsetParent;
      }
    }//if offsetParent exists
    else if (obj.x && obj.y) {
      y += obj.y
      x += obj.x
    }
	return new coordinate(x, y);
}//findElementPos


// get dimensions of the browser window
function getClientDim() {
	var nwX, nwY, seX, seY;
	if (self.pageYOffset) // all except Explorer
	{
	  nwX = self.pageXOffset;
	  seX = self.innerWidth + nwX;
	  nwY = self.pageYOffset;
	  seY = self.innerHeight + nwY;
	}
	else if (document.documentElement && document.documentElement.scrollTop) // Explorer 6 Strict
	{
	  nwX = document.documentElement.scrollLeft;
	  seX = document.documentElement.clientWidth + nwX;
	  nwY = document.documentElement.scrollTop;
	  seY = document.documentElement.clientHeight + nwY;
	}
	else if (document.body) // all other Explorers
	{
	  nwX = document.body.scrollLeft;
	  seX = document.body.clientWidth + nwX;
	  nwY = document.body.scrollTop;
	  seY = document.body.clientHeight + nwY;
	}
	return {'nw' : new coordinate(nwX, nwY), 'se' : new coordinate(seX, seY)};
} 

/**
* stop propagation on events
**/
function freezeEvent(e) {
	if(e) {
	  if (e.preventDefault) e.preventDefault();
	  e.returnValue = false;
	  e.cancelBubble = true;
	  if (e.stopPropagation) e.stopPropagation();
	  return false;
	}
}


/**
 * coordinate class
 **/
function coordinate(_x, _y) {
  var x = _x;
  var y = _y;
  this.add = add;
  this.sub = sub;
  this.x = x;
  this.y = y;

  function add(rh) {
    return new position(this.x + rh.x, this.y + rh.y);
  }

  function sub(rh) {
    return new position(this.x + rh.x, this.y + rh.y);
  }
}

// -- start sugarListView class
// js functions used for ListView
function sugarListView() {
}

sugarListView.update_count = function(count, add) {
	if(typeof document.MassUpdate != 'undefined') {
		the_form = document.MassUpdate;
		for(wp = 0; wp < the_form.elements.length; wp++) {
			if(typeof the_form.elements[wp].name != 'undefined' && the_form.elements[wp].name == 'selectCount[]') {
				if(add)	the_form.elements[wp].value = parseInt(the_form.elements[wp].value) + count;
				else the_form.elements[wp].value = count;
			}	
		}
	}
}

sugarListView.prototype.send_form = function(select, currentModule, action, no_record_txt) {
	sugarListView.get_checks();
	// create new form to post (can't access action property of MassUpdate form due to action input) 
	var newForm = document.createElement('form');
	newForm.method = 'post';
	newForm.action = action;
	newForm.name = 'newForm';
	newForm.id = 'newForm';
	
	var uidTa = document.createElement('textarea');
	uidTa.name = 'uid';
	uidTa.style.display = 'none';
	if(select) { // use selected items	
		uidTa.value = document.MassUpdate.uid.value;
	}
	else { // use current page
		inputs = document.MassUpdate.elements;
		ar = new Array();
		for(i = 0; i < inputs.length; i++) {
			if(inputs[i].name == 'mass[]')
				ar.push(inputs[i].value);
		}
		uidTa.value = ar.join(',');
	}
	
	if(uidTa.value == '') { 
		alert(no_record_txt); 
		return false;
	}
	
	newForm.appendChild(uidTa);
	
	var moduleInput = document.createElement('input');
	moduleInput.name = 'module';
	moduleInput.type = 'hidden';
	moduleInput.value = currentModule;	
	newForm.appendChild(moduleInput);
	
	var actionInput = document.createElement('input');
	actionInput.name = 'action';
	actionInput.type = 'hidden';
	actionInput.value = 'index';	
	newForm.appendChild(actionInput);

	document.MassUpdate.parentNode.appendChild(newForm);

	newForm.submit();

	return false;
}

// saves the checks on the current page into the uid textarea
sugarListView.get_checks = function() {
	ar = new Array();

	if(document.MassUpdate.uid.value != '') {
		oldUids = document.MassUpdate.uid.value.split(',');
		for(uid in oldUids) ar[oldUids[uid]] = 1;	
	}
	
	// build associated array of uids, associated array ensures uniqueness	
	inputs = document.MassUpdate.elements;
	for(i = 0; i < inputs.length; i++) {
		if(inputs[i].name == 'mass[]') 
			ar[inputs[i].value]	= (inputs[i].checked) ? 1 : 0; // 0 of it is unchecked
	}
	
	// build regular array of uids
	uids = new Array(); 
	for(i in ar) { 
		if(ar[i] == 1) uids.push(i);
	}
	
	document.MassUpdate.uid.value = uids.join(',');

	if(uids.length == 0) return false; // return false if no checks to get 
	return true; // there are saved checks
}

sugarListView.prototype.save_checks = function(offset, moduleString) {
	checks = sugarListView.get_checks();
	eval('document.MassUpdate.' + moduleString + '.value = offset');
	if(typeof document.MassUpdate.massupdate != 'undefined') document.MassUpdate.massupdate.value = 'false';
	if(checks) document.MassUpdate.submit();
	return !checks;
}

sugarListView.prototype.check_item = function(cb, form) {
	if(cb.checked) sugarListView.update_count(1, true);
	else sugarListView.update_count(-1, true);
}

sugarListView.prototype.check_all = function(form, field, value) {
	// count number of items
	count = 0;
	
	for (i = 0; i < form.elements.length; i++) {
		if(form.elements[i].name == field) {
			if(form.elements[i].checked != value) count++;
			form.elements[i].checked = value;
		}
	}

	if(value) sugarListView.update_count(count, true);
	else sugarListView.update_count(-1 * count, true);
}
sugarListView.check_all = sugarListView.prototype.check_all;

sugarListView.prototype.check_boxes = function() {
	var inputsCount = 0;	
	var checkedCount = 0;
	var existing_onload = window.onload;
	var form = document.MassUpdate;

	if(form.uid.value != "") {
		inputs_array = form.elements;
		checked_items = form.uid.value.split(",");
		for(wp = 0 ; wp < inputs_array.length; wp++) {
			if(inputs_array[wp].name == "mass[]") {
				inputsCount++;				
				for(i in checked_items) {
					if(inputs_array[wp].value == checked_items[i]) {
						checkedCount++;
						inputs_array[wp].checked = true;
					}
				}
			}
		}
		sugarListView.update_count(checked_items.length);
	}
	else {
		sugarListView.update_count(0)
	}
	if(checkedCount > 0 && checkedCount == inputsCount) 
		document.MassUpdate.massall.checked = true;
}

sugarListView.prototype.send_mass_update = function(mode, no_record_txt, del) {
	var ar = new Array();
	if(del == 1) {
		var deleteInput = document.createElement('input');
		deleteInput.name = 'Delete';
		deleteInput.type = 'hidden';
		deleteInput.value = true;	
		document.MassUpdate.appendChild(deleteInput);
	}
	
	switch(mode) {
		case 'page': 
			document.MassUpdate.uid.value = '';
			for(wp = 0; wp < document.MassUpdate.elements.length; wp++) {
				if(typeof document.MassUpdate.elements[wp].name != 'undefined' 
					&& document.MassUpdate.elements[wp].name == 'mass[]') {
							ar.push(document.MassUpdate.elements[wp].value);
				}
			}			
			document.MassUpdate.uid.value = ar.join(',');
			if(document.MassUpdate.uid.value == '') {
				alert(no_record_txt);
				return false;
			}
			break;
		case 'selected':
			for(wp = 0; wp < document.MassUpdate.elements.length; wp++) {
				if(typeof document.MassUpdate.elements[wp].name != 'undefined' 
					&& document.MassUpdate.elements[wp].name == 'mass[]' 
						&& document.MassUpdate.elements[wp].checked) {
							ar.push(document.MassUpdate.elements[wp].value);
				}
			}			
			if(document.MassUpdate.uid.value != '') document.MassUpdate.uid.value += ',';
			document.MassUpdate.uid.value += ar.join(',');
			if(document.MassUpdate.uid.value == '') {
				alert(no_record_txt);
				return false;
			}
			break;
		case 'entire': 
			var entireInput = document.createElement('input');
			entireInput.name = 'entire';
			entireInput.type = 'hidden';
			entireInput.value = 'index';	
			document.MassUpdate.appendChild(entireInput);
			confirm(no_record_txt);
			break;	
	}
				
	document.MassUpdate.submit();
	return false;
}


sugarListView.prototype.clear_all = function() {
	document.MassUpdate.uid.value = '';
	sugarListView.check_all(document.MassUpdate, 'mass[]', false);
	document.MassUpdate.massall.checked = false;
	sugarListView.update_count(0);
}

sListView = new sugarListView();
// -- end sugarListView class

// format and unformat numbers
function unformatNumber(n, num_grp_sep, dec_sep) {
	if(typeof num_grp_sep == 'undefined' || typeof dec_sep == 'undefined') return n;
	n = n.toString();
	if(n.length > 0) {
		nsplit = n.split(dec_sep);
		if(nsplit.length == 2) {
			n = nsplit[0].replace(new RegExp(RegExp.escape(num_grp_sep), 'g'), '') + '.' + nsplit[1];
		}
		else  {
			n = nsplit[0].replace(new RegExp(RegExp.escape(num_grp_sep), 'g'), '');
		}
		return parseFloat(n);
	}
	return '';
}

// round parameter can be negative for decimal, precision has to be postive
function formatNumber(n, num_grp_sep, dec_sep, round, precision) {
  if(typeof num_grp_sep == 'undefined' || typeof dec_sep == 'undefined') return n;
  n = n.toString();
  if(n.split) n = n.split('.');
  else return n;

  if(n.length > 2) return n.join('.'); // that's not a num!
  // round
  if(typeof round != 'undefined') {
    if(round > 0 && n.length > 1) { // round to decimal
      n[1] = parseFloat('0.' + n[1]);
      n[1] = Math.round(n[1] * Math.pow(10, round)) / Math.pow(10, round);
      n[1] = n[1].toString().split('.')[1];
    }
    if(round <= 0) { // round to whole number
      n[0] = Math.round(parseInt(n[0]) * Math.pow(10, round)) / Math.pow(10, round);
      n[1] = '';
    }
  }

  if(typeof precision != 'undefined' && precision >= 0) {
    if(n.length > 1 && typeof n[1] != 'undefined') n[1] = n[1].substring(0, precision); // cut off precision 
	else n[1] = '';
    if(n[1].length < precision) {
      for(var wp = n[1].length; wp < precision; wp++) n[1] += '0';
    }
  }

  regex = /(\d+)(\d{3})/;
  while(regex.test(n[0])) n[0] = n[0].replace(regex, '$1' + num_grp_sep + '$2');
  return n[0] + (n.length > 1 && n[1] != '' ? dec_sep + n[1] : '');
}
