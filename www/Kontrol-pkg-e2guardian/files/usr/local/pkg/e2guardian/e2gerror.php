<?php

/*
 * e2gerror.php
 *
 * part of pfSense (https://www.pfsense.org)
 * Copyright (c) 2020 Marcello Coutinho
 * Copyright (c) 2020 Rubicon Communications, LLC (Netgate)
 * All rights reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

// Based on Pfsensation (GitHub @Forid786) template.html

// you can translate via gettext or directly on these vars

//Leave blank to disable denied log file.
define('LOG_DENIED', 'none');
$now = date("Y-m-d H:i:s");
$access_denied = gettext("Access Denied");
$oops1 = gettext("Oops!");
$oops2 = gettext("You have tried visiting a website which has been deemed inappropriate");
$because = gettext("Because");
$details = gettext("Your details are below");
$ack = gettext("Acknowledge");

// end of translate text
$allow_html_code = 0;
$in = &ReadEnvs();

$deniedurl = $in['DENIEDURL'];
$reason = $in['REASON'];
$user = $in['USER'];
$ip = $in['IP'];
$cats = $in['CATEGORIES'];
$group = ( $in['FILTERGROUP'] ? $in['FILTERGROUP'] : "-" );

# originating hostname - can be undefined

if (strlen($in['HOST']) > 0) {
	$host = $in['HOST'] . "({$in['IP']})";
} else {
	$host = gethostbyaddr($in['IP']) . "({$in['IP']})";
}

# virus/filter bypass hashes
# if bypass modes have been set to > 0,
# then the GBYPASS or GIBYPASS variable will contain the filter/infection bypass hash.
# if bypass modes have been set to -1,
# then the HASH variable will be set to 1 if the CGI should generate a GBYPASS hash (filter bypass),
# or 2 if the CGI should generate a GIBYPASS hash (infection bypass).

$fbypasshash = $in['GBYPASS']; # filter bypass hash - can be undefined
$ibypasshash = $in['GIBYPASS']; # infection bypass hash - can be undefined
$hashflag = $in['HASH']; # hash flag - can be undefined; 1 = generate GBYPASS; 2 = generate GIBYPASS

$bypass = $deniedurl;
$prefix = (preg_match("/\?/",$deniedurl) ? "&" : "?");
if ( array_key_exists('GBYPASS',$in)) {
	$bypass .= $prefix . "GBYPASS=" . $in['GBYPASS'];
} else if ( array_key_exists('GIBYPASS',$in)) {
	$bypass .= $prefix . "GIBYPASS=" . $in['GIBYPASS'];
}
$user_info = "-";
if (strlen($user) > 0) {
	$user_info = $user;
}

if (strlen($cats) > 0) {
  $cats_info = 'categories:<BR>$cats';
}

function ReadEnvs () {
  global $allow_html_code;
  $in = array();
  if (isset($_SERVER['QUERY_STRING'])) {
 	$clp = preg_split("/::/", $_SERVER['QUERY_STRING']);

	foreach ($clp as $pair) {
		$name = $value = "";
		list($name, $value) = (preg_split("/==/", $pair));
		$value = urldecode($value);
		$value = preg_replace("/\+/", " ", $value);
		$value = preg_replace("/\|/", " | ", $value);
		$value = preg_replace("/\<\!--.*--\>/", "", $value);
		if ($allow_html_code != 1) {
			$value = preg_replace("/\<.*\>/",'',$value);
		}
		$in[$name] = $value;
	}
 }
 return $in;
}

$html = <<<EOF

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">


		<title>KONTROL - Accesso Negado</title>
		<meta charset="UTF-8">
		<!-- Ensure local favicon is loaded or site's is blanked, some sites have shady favicons. -->
		<!-- <link rel="shortcut icon" href="favicon.ico"> -->
		<style type="text/css">
			body {
				color:#000000;
				background-color:#ffffff;
				font-family:arial, helvetica, sans-serif;
			}
			img.valid {
				width:88px;
				height:31px;
				border-width:0px;
			}
			table.main {
				width:700px;
				height:540px;
				padding:2px;
				border-width:0px;
				margin-left:auto;
				margin-right:auto;
			}
			td.content {
				width:550px;
				vertical-align:middle;
				color:#000000;
				background-color:#ffffff;
				font-size:14pt;
				text-align:center;
			}
			td.notice {
				height:100px;
				color:#000000;
				/* background-color:#fea700; */
				background-color:#ff0000;
				font-size:22pt;
				font-weight:bold;
				text-align:center;
			}
			td.org {
				width:150px;
				vertical-align:bottom;
				color:#000000;
				background-color:#b0c4de;
				font-size:8pt;
				text-align:center;
			}
			td.user {
				height:30px;
				color:#000000;
				/* background-color:#fffacd; */
				background-color:#999999;
				font-size:12pt;
				font-weight:bold;
				text-align:right;
			}
		</style>





<Style>
 .rtop,.rbottom{display:block}
 .rtop *,.rbottom *{display:block;height: 1px;overflow: hidden}
 .r1{margin: 0 5px}
 .r2{margin: 0 3px}
 .r3{margin: 0 2px}
 .r4{margin: 0 1px;height: 2px}
 .rs1{margin: 0 2px}
 .rs2{margin: 0 1px}
 .bypasstext{ font-size:10px;}
 .errortext { color: red;}
 body{ padding: 20px; background-color: #1f2229; font: 100.01% "Trebuchet MS",Verdana,Arial,sans-serif; }
 div#nifty{ margin: 0 10%;background: #454b5a; }
</Style>
<Script type="text/javascript">
function NiftyCheck() { if(!document.getElementById || !document.createElement)
return(false); var b=navigator.userAgent.toLowerCase(); if(b.indexOf("msie 5")>0 && b.indexOf("opera")==-1)
return(false); return(true);}
function Rounded(selector,bk,color,size){ var i; var v=getElementsBySelector(selector); var l=v.length; for(i=0;i<l;i++){ AddTop(v[i],bk,color,size); AddBottom(v[i],bk,color,size);}
}
function RoundedTop(selector,bk,color,size){ var i; var v=getElementsBySelector(selector); for(i=0;i<v.length;i++)
AddTop(v[i],bk,color,size);}
function RoundedBottom(selector,bk,color,size){ var i; var v=getElementsBySelector(selector); for(i=0;i<v.length;i++)
AddBottom(v[i],bk,color,size);}
function AddTop(el,bk,color,size){ var i; var d=document.createElement("b"); var cn="r"; var lim=4; if(size && size=="small"){ cn="rs"; lim=2}
d.className="rtop"; d.style.backgroundColor=bk; for(i=1;i<=lim;i++){ var x=document.createElement("b"); x.className=cn + i; x.style.backgroundColor=color; d.appendChild(x);}
el.insertBefore(d,el.firstChild);}
function AddBottom(el,bk,color,size){ var i; var d=document.createElement("b"); var cn="r"; var lim=4; if(size && size=="small"){ cn="rs"; lim=2}
d.className="rbottom"; d.style.backgroundColor=bk; for(i=lim;i>0;i--){ var x=document.createElement("b"); x.className=cn + i; x.style.backgroundColor=color; d.appendChild(x);}
el.appendChild(d,el.firstChild);}
function getElementsBySelector(selector){ var i; var s=[]; var selid=""; var selclass=""; var tag=selector; var objlist=[]; if(selector.indexOf(" ")>0){ s=selector.split(" "); var fs=s[0].split("#"); if(fs.length==1) return(objlist); return(document.getElementById(fs[1]).getElementsByTagName(s[1]));}
if(selector.indexOf("#")>0){ s=selector.split("#"); tag=s[0]; selid=s[1];}
if(selid!=""){ objlist.push(document.getElementById(selid)); return(objlist);}
if(selector.indexOf(".")>0){ s=selector.split("."); tag=s[0]; selclass=s[1];}
var v=document.getElementsByTagName(tag); if(selclass=="")
return(v); for(i=0;i<v.length;i++){ if(v[i].className==selclass){ objlist.push(v[i]);}
}
return(objlist);}
window.onload=function(){ if(!NiftyCheck())
return; Rounded("div#nifty","#377CB1","#9BD1FA");}
</Script>
</HTML>
<Body>

 <Table Border="0" width="100%" height="100%">
  <TD valign="middle" align="center">

<img src= "data:image/png;base64,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"
 alt="ACCESS DENIED" align="middle">

  <div id="nifty">
    <P>
    <font color="red"><h2>PÁGINA BLOQUEADA:</h2></font><BR>
	<font color="white">Esta página foi bloqueada pelas possíveis razões:</font><BR>
	<font color="white">*A página tem conteúdo considerado inapropriado.</font><BR>
	<font color="white">*Seu usuário de rede está em um grupo com políticas de restrição.</font><BR> <BR>
	<font color="white">Se você acredita, por alguma razão, que este bloqueio é indevido,</font><BR>
	<font color="white">entre em contato com o administrador da rede.</font> <font color="white"> <a href="mailto:administrator@corporation.corp?Subject=Website blocked by Kontrol">E-mail</a></font><BR>
      <BR>
     <BR>
	 <font color="black"><B>URL Bloqueada:</B></font> <font color="white"><I>* -URL- *</I></font>
      <BR><BR><BR>
      <font color="red"><B>Categoria ou Motivo do Bloqueio:</B></font><BR>
      <BR>
      <span class="errortext">Categoria:&nbsp;-CATEGORIES-</span>&nbsp;&nbsp;
      <span class="errortext">-REASONGIVEN-</span>
      <BR><BR><BR>
      <h4>Seus Dados Abaixo:</h4>
      <B>Usuário:</B><font color="white">&nbsp;-USER-&nbsp&nbsp;&nbsp;</font><B>Grupo:</B><font color="white">&nbsp;-FILTERGROUP-&nbsp;&nbsp;&nbsp;</font><B>IP:</B><font color="white">&nbsp;-IP-</font>
      <BR>
	  <BR>
	  <img src=
"data:image/png;base64,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"
<alt="KONTROL LOGO" align="middle">
<BR>
	<!– Bypass link below –>
      <a href="-BYPASS-" class="bypasstext">ByPass</a>
   </P>
   </div>
  </TD>
 </Table>
</Body>

EOF;

print $html;

if (LOG_DENIED != "none") {
	file_put_contents(LOG_DENIED,"$now;;$user_info;;$ip;;$deniedurl;;$cats;;$reason;;$group\n",FILE_APPEND);
}
?>
