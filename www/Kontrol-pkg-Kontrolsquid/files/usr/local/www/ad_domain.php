<?php
/*
 * ad_domain.php
 *
 * KONNTROL TECNOLOGIA EPP - Copyright - 2016-2020
 * KONTROL-UTM - is Registered Brand of KONNTROL TECNOLOGIA EPP.
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

##|+PRIV
##|*IDENT=kontrolauth-config-page
##|*NAME=Services: AD_DOMAIN
##|*DESCR=Allow access to the 'Services: AD_DOMAIN' page.
##|*MATCH=ad_domain.php*
##|-PRIV
include ("guiconfig.inc");
include("head.inc");

?>

<html> 
<head> 
</head>
<body> 
	<h3> Remove KONTROL-UTM from your DOMAIN </h3>
<br/>
	<form name="form_a" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
	<label>Enter Username with admin permissions - eg: administrator</label><br/>
	<input type="text" name="rad_user" size="120" maxlength="120"/>
<br/>
<br/>
	<label>Password:</label>
<br/>
	<input type="password" name="rad_pass" size="120" maxlength="120"/><br/>
	<input type="hidden" name="form" value="remove">
<br/>
	<input type="submit" value="Submit"/>
	<input type="reset" value="Reset"/>

<br/>
</form>
<br/>
<?php
$testdomain = exec("net ads testjoin");
if ($testdomain == "Join is OK") {
	echo "<tr><span style='color:#F00;text-align:center;'>This KONTROL-UTM box IS ALREADY part of a DOMAIN</span></tr>";
}
		else {
			echo "<span style='color:#F00;text-align:center;'>This KONTROL-UTM box is NOT yet part of a DOMAIN</span>";
			}
?>

	<h3> Join KONTROL-UTM to a DOMAIN </h3>
<br/>	
<form name="form_j" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
	<label>Enter AD Server FQDN - eg: server01.domain.corp</label>
<br/>
	<input type="text" name="jad_srv" size="120" maxlength="120"/>
<br/>
<br/>
	<label>Enter Interface name separated by space (Use LAN ONLY!) - ex: re0 re1 hn0 hn1 igb0 </label>
<br/>
	<input type="text" name="j_iface" size="120" maxlength="120"/>
<br/>
<br/>
	<label>Enter Username with admin permissions - eg: administrator</label>
<br/>
	<input type="text" name="jad_user" size="120" maxlength="120"/>
<br/>
<br/>
	<label>Password:</label>
<br/>
	<input type="password" name="jad_pass" size="120" maxlength="120"/><br/>
	<input type="hidden" name="form" value="join">
<br/>
	
	<input type="submit" value="Submit"/>
	<input type="reset" value="Reset"/>
<br/>
<br/>
</form>	


<?php
$file = "/usr/local/www/kontrolhelper.config";
if (!file_exists($file)) 
	{
	echo "<tr><span style='color:#F00;text-align:center;'>The configuration file does not exist. Create one below: </span></tr>";
	}
	else 
	{
		$f = fopen($file, 'r');
		$line = fgets($f);
		fclose($f);
		echo "<span style='color:#F00;text-align:center;'>The configuration file exists and it points to: -  $line </span>";
	}
?>
	<h3> Transparent Proxy Kontrol-ID Config </h3>
<br/>
	<form name="form_c" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
	<label>Enter AD-Server or Kontrol-Master IP Address - eg: 192.168.0.10</label><br/>
	<input type="text" name="tad_srv" size="120" maxlength="120"/>
	<input type="hidden" name="form" value="transparent">
<br/>
<br/>
	<input type="submit" value="Submit"/>
	<input type="reset" value="Reset"/>
</form>


</body>
</html>

<br/>
<br/>



<?php

//Declaring Variables
$radusername = $_POST["rad_user"];
$radusername = addslashes($radusername);
$radpassword = $_POST["rad_pass"];
$radpassword = addslashes($radpassword);

$srvname = $_POST["jad_srv"];
$jadusername = $_POST["jad_user"];
$jadusername = addslashes($jadusername);
$jadpassword = $_POST["jad_pass"];
$jadpassword = addslashes($jadpassword);
$jiface = $_POST["j_iface"];

$tadsrvname = $_POST["tad_srv"];

$domain = (exec ('hostname -d'));
$ad_domain = strtoupper(exec ('hostname -d'));
$host_var = exec ("hostname");
$smb_workgroup = exec('hostname -d | cut -d. -f1');
$smb_workgroup_upper = strtoupper($smb_workgroup);


// Checking what FORM is running (remove, join or transparent)
	if (isset($_POST['form'])){

switch ($_POST['form']) {

//in case we want to remove kontrol from domain
case "remove":
		if (isset($_POST['rad_user']) && ($_POST['rad_pass'])) {
?>

<div style="height:200px;width:870px;overflow:auto;background-color:gray;color:white;scrollbar-base-color:gold;font-family:sans-serif;padding:10px;">	

<?php
		echo "Removing this KONTROL-UTM box from your DOMAIN: ";
		echo "<br/>";
		echo exec ("net ads leave -U $radusername%$radpassword");
		echo "<br/>";
		echo "Deleting existing keytab: "."<br/>";
		echo exec ('rm /etc/krb5.keytab 2>&1');
		echo "<br/>";
		echo "Killing all kontrolAuth Processes (if exists):";
		echo "<br/>";
		echo exec ('killall winbindd 2>&1');
		echo "<br/>";
		echo "FINISHED";
		break;
}       
		else
			{
?>			
<div style="height:200px;width:870px;overflow:auto;background-color:gray;color:white;scrollbar-base-color:gold;font-family:sans-serif;padding:10px;">			
<?php
	echo "<h3>Please fullfill all empty fields before press submit<h3>";
	break;
			}

//in case we want to join kontrol to a domain
case "join":            

				if (isset($_POST['jad_user']) && ($_POST['jad_pass'])) 
				{
?>

<div style="height:200px;width:870px;overflow:auto;background-color:gray;color:white;scrollbar-base-color:gold;font-family:sans-serif;padding:10px;">	

<?php
			exec ("sed -i \"\" \"9s/.*/define('DOMAIN_FQDN', '$domain');/\" /usr/local/www/login2.php");
			exec ("sed -i \"\" \"10s/.*/define('LDAP_SERVER', '$srvname');/\" /usr/local/www/login2.php");
			exec ("sed -i \"\" \"s/^interfaces.*/interfaces = lo0 $jiface /\" /usr/local/etc/smb4.conf");
			exec ("sed -i \"\" \"s/^workgroup.*/workgroup = $smb_workgroup_upper /\" /usr/local/etc/smb4.conf");
			exec ("sed -i \"\" \"s/^realm.*/realm  = $ad_domain /\" /usr/local/etc/smb4.conf");
			exec ("sed -i \"\" \"7s/.*/idmap config $smb_workgroup_upper : backend = rid /\" /usr/local/etc/smb4.conf");
			exec ("sed -i \"\" \"8s/.*/idmap config $smb_workgroup_upper : range = 10000-20000 /\" /usr/local/etc/smb4.conf");
			exec ("sed -i \"\" \"2s/.*/default_realm = $ad_domain /\" /usr/local/etc/krb5.conf");
			exec ("sed -i \"\" \"14s/.*/   $ad_domain = { /\" /usr/local/etc/krb5.conf");
			exec ("sed -i \"\" \"15s/.*/   kdc = $srvname /\" /usr/local/etc/krb5.conf");
			exec ("sed -i \"\" \"16s/.*/   admin_server = $srvname /\" /usr/local/etc/krb5.conf");
			exec ("sed -i \"\" \"17s/.*/   default_domain = $domain /\" /usr/local/etc/krb5.conf");
			exec ("sed -i \"\" \"21s/.*/  .$domain = $ad_domain /\" /usr/local/etc/krb5.conf");
			exec ("sed -i \"\" \"22s/.*/   $domain = $ad_domain /\" /usr/local/etc/krb5.conf");
			
			echo "Joining this KONTROL-UTM box to your DOMAIN: "."<br>";
			echo "Cleaning existing credentials: "."<br>";
			exec ('kdestroy 2>&1');
			echo "Starting session to Join Domain: ";
			echo "<br/>";
			echo exec ("echo $jadpassword | kinit $jadusername 2>&1");
				if (file_exists("/etc/krb5.keytab")){
					echo exec ('rm /etc/krb5.keytab 2>&1');
					echo "Deleting Existing Keytab";
					echo "<br/>";
				}
			echo "<br/>";
			echo "Joining domain: $ad_domain ";
			echo "<br/>";
			echo exec ("net ads join createupn=HTTP/$host_var@$ad_domain -k");
			echo "Adding the SPN HTTP: ";
			echo "<br/>";
			echo exec ('net ads keytab add HTTP 2>&1');
			echo "<br/>";
			echo "Creating a new keytab file";
			echo "<br/>";
			echo exec ("net ads keytab create -k 2>&1");
			echo "<br/>";
			echo exec ("chown root:proxy /var/db/samba4/winbindd_privileged 2>&1");
			echo exec ("chmod -R 0750 /var/db/samba4/winbindd_privileged 2>&1");
			echo "Restarting WINBINND DAEMON MODE";
			echo "<br/>";
			echo exec ('killall winbindd 2>&1');
			echo "<br/>";
			echo exec ('/usr/local/sbin/winbindd --daemon --configfile=/usr/local/etc/smb4.conf 2>&1');
			echo exec ('chown root:proxy /etc/krb5.keytab');
			echo exec ('chmod 0440 /etc/krb5.keytab 2>&1');
			echo exec ('ktutil -k /etc/krb5.keytab list 2>&1');
			echo "<br/>";
			exec ("/usr/local/sbin/pfSsh.php playback svc restart squid");
			exec ('chown root:proxy /var/db/samba4/winbindd_privileged');
			echo "FINISHED";
			break;
			}	
			else
			{
?>			
<div style="height:200px;width:870px;overflow:auto;background-color:gray;color:white;scrollbar-base-color:gold;font-family:sans-serif;padding:10px;">			
<?php
	echo "<h3>Please fullfill all empty fields before press submit<h3>";
	break;
			}
		
		
			
// In case we want to configure Transparent Proxy credentials			
case "transparent":

	if (isset($_POST['tad_srv'])) {
?>		
<div style="height:200px;width:870px;overflow:auto;background-color:gray;color:white;scrollbar-base-color:gold;font-family:sans-serif;padding:10px;">		
<?php			
			echo "Setting Transparent Proxy Configurations: ";
			echo "<br/>";
			exec ("sed -i \"\" \"9s/.*/define('DOMAIN_FQDN', '$domain');/\" /usr/local/www/login2.php");
			exec ("sed -i \"\" \"10s/.*/define('LDAP_SERVER', '$tadsrvname');/\" /usr/local/www/login2.php");
						
			if (!file_exists($file)) 
			{
			touch($file);
			}
			
			file_put_contents($file, $tadsrvname);     // Save our content to the file.
						
			echo "FINISHED";
			echo "<br/>";
		}
		else
			{
?>			
<div style="height:200px;width:870px;overflow:auto;background-color:gray;color:white;scrollbar-base-color:gold;font-family:sans-serif;padding:10px;">			
<?php
	echo "<h3>Please fullfill all empty fields before press submit<h3>";
	break;
			}
		
	}
	
}

include("foot.inc");
?>
