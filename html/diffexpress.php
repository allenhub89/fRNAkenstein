<!--
######################################
# fRNAkenstein                       #
#   by Allen Hubbard & Wayne Treible #
#                                    #
# A front-end interface for the      #
# tuxedo pipeline including Tophat,  #
# Cufflinks, and Cuffdiff.           #
#                                    #
# Version 0.10 Updated 6/17/2014     #
######################################
-->

<?php 
####################################
# Required File Structure:         #
#                                  #
# subdirectories/                  #
#   --fastq_to_be_crunched/        #
#   --fasta_directory/             #
#   --annotation_directory/        #
#   --temp_output/                 #
#   --bash_scripts/                #
#   --thcl_output/                 #
#   --logs/                        #
#                                  #
# Modify $subdirectories to change #
#   the root of the file system    #
####################################

$subdirectories = "/var/www/subdirectories_for_interface";


?>

<head>
<title>
fRNAkenstein - DiffExpress
</title>
<link rel="STYLESHEET" type="text/css" href="css_dir/style.css">
<link rel="icon" type="image/ico" href="images/favicon.ico"/>

<!--
##############################################
# Initilaize PhP variables for jQuery script #
##############################################
-->

<?php
# Can modify later with real values
$archivedlibs = scandir("$subdirectories/thcl_output");
$archivedlibs = preg_replace(array("/(.*)library_/","/\./","/\.\./"), "", $archivedlibs);

?>

<!--
##########################
# Archive Checkbox Adder #
##########################
-->

<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script>

$(document).ready(function(){
	var libs = <?php echo json_encode($archivedlibs); ?>;


	$("#ctrlchk").click(function(){
		var inp = $("#ctrltxt");
		if(inp.val().length > 0) {
			var library = inp.val();
			if(jQuery.inArray(library, libs) != -1)
			{
				var $ctrl = $('<input/>').attr({ type: 'checkbox', name:'controlfilename[]', value: 'library_'+library}).addClass("ctrlchk");
				$("#ctrlholder").append($ctrl);
				$("#ctrlholder").append(library);
				$("#ctrlholder").append("<br>");
				libs = jQuery.grep(libs, function(value) {
  					return value != library;
				});
			}
			else {
				alert("Library \'" + library + "\' is not in the archive or already added as a separate condition.");
			}
		}
		else {
			alert("Please enter a library number");
		}
		
	});

	$("#expchk").click(function(){
		var inp = $("#exptxt");
		if(inp.val().length > 0) {
			var library = inp.val();
			if(jQuery.inArray(library, libs) != -1)
			{
				var $exp = $('<input/>').attr({ type: 'checkbox', name:'expfilename[]', value: 'library_'+library}).addClass("expchk");
				$("#expholder").append($exp);
				$("#expholder").append(library);
				$("#expholder").append("<br>");
				libs = jQuery.grep(libs, function(value) {
  					return value != library;
				});
			}
			else {
				alert("Library \'" + library + "\' is not in the archive or already added as a separate condition.");
			}
		}
		else {
			alert("Please enter a library number");
		}
		
	});
});
</script>



<!--
######################
# Checkbox Validator #
######################
-->

<script language="javascript">
function valthisform()
{
	var controlcheckboxs=document.getElementsByName("controlfilename[]");
	var expcheckboxs=document.getElementsByName("expfilename[]");
	var okay=false;
	for(var i=0,l=controlcheckboxs.length;i<l;i++)
	{
		for(var ii=0,ll=expcheckboxs.length;ii<ll;ii++)
		{
			if(controlcheckboxs[i].checked && expcheckboxs[ii].checked)
			{
	   			okay=true;
			}
		}
	}
	if(okay){
		/*document.getElementById('crunch').disabled = 1*/
		alert("Running DiffExpress on Data!");
	}
	else alert("Please select both libraries!");
	return okay;
}

</script>

</head>
<body>
<center>
<!--
############################
# Beginning of submit form #
############################
-->
<style type="text/css">
    .fieldset-auto-width {
         display: inline-block;
    }
</style>
<div>
<fieldset class="fieldset-auto-width">

<legend>
<h3>
<!img src="images/favicon.png" alt="fRNAk" width="24" height="24"> 
fRNAkenstein - DiffExpress
<!img src="images/favicon.png" alt="fRNAk" width="24" height="24">
</h3>
</legend>
<form id='submitform' onsubmit="return valthisform(this);" action='/diffexpress_response.php' method='post' target='formresponse'>



<!--
################################
# Beginning of alignment table #
################################
-->

<table height="90%" style="margin: 0px;">

<!--
##########################################################
# Create Checkboxes for control library files (lib nums) #
##########################################################
-->

<tr style="padding:0px; margin:0px;">
<th valign="top" align="left" style="padding-top:12px;padding-left:8px;width:300px">

<div class='container'>
<h4>Control Condition:</h4> <input type="text" name="controlcondition"><br>

<?php
$controllibs = scandir("$subdirectories/thcl_output");

# Sorts files by "natural human sorting" such that:
# 1.ext                       1.ext
# 10.ext     ==becomes==>     2.ext
# 2.ext                       10.ext 
if(!empty($controllibs))
{
  natsort($controllibs);
}

echo "<h4>Choose control library number(s):</h4>";
foreach($controllibs as $library)
{
  if ($library !== "." and $library !== "..")
  { 
    $librarynum = "";
    $libpattern = "/\D*(.*)/";
    preg_match($libpattern, $library, $matches);
    $librarynum = $matches[1];
    echo "<input type=\"checkbox\" name=\"controlfilename[]\" value=\"$library\">$librarynum<br>";
  }
} 

echo "</select>";

?>

<br>

</div>

<!--
##############################
# Archived Control Libraries #
##############################
-->

<div class='container'>
<h4>Add archived control library:</h4> 

<div id='ctrlholder' style='padding-bottom:10px'>


</div>
<input type="text" id="ctrltxt"> <button id="ctrlchk" type="button">+</button>

</div>

</th>

<!--
###############################################################
# Create Checkboxes for experimental library files (lib nums) #
###############################################################
-->

<th valign="top" align="left" style="padding-top:12px;padding-left:8px;width:300px">
<div class='container'>

<h4>Experimental Condition:</h4> <input type="text" name="expcondition"><br>

<?php
$explibs = scandir("$subdirectories/thcl_output");

# Sorts files by "natural human sorting" such that:
# 1.ext                       1.ext
# 10.ext     ==becomes==>     2.ext
# 2.ext                       10.ext 
if(!empty($explibs))
{
  natsort($explibs);
}

echo "<h4>Choose experimental library number(s):</h4>";
foreach($explibs as $explibrary)
{
  if ($explibrary !== "." and $explibrary !== "..")
  { 
    $librarynum = "";
    $libpattern = "/\D*(.*)/";
    preg_match($libpattern, $explibrary, $matches);
    $librarynum = $matches[1];
    echo "<input type=\"checkbox\" name=\"expfilename[]\" value=\"$explibrary\">$librarynum<br>";
  }
} 

echo "</select>";

?>

<br>

</div>


<!--
##############################
# Archived Control Libraries #
##############################
-->

<div class='container'>
<h4>Add archived experimental library:</h4> 

<div id='expholder' style='padding-bottom:10px'>


</div>
<input type="text" id="exptxt"> <button id="expchk" type="button">+</button>

</div>

</th>

<!--
#######################
# iFrame for Response #
#######################
-->

<th rowspan="2" valign="top" style="padding-left:0px;align:left">
<br>
<iframe id='frame' name='formresponse' style="border: outset;height: 95% ; background-color:#d0eace" width='500px' frameborder='0'>


</iframe>

</tr>

<!--
#############################
# Row for form and response #
#############################
-->

<tr style="padding:0px; margin:0px;">
<td colspan="2" valign="top" style="padding-top:12px;padding-left:8px;width:600px">

<!--
######################################
# Proc Selector Slider (JS onchange) #
######################################
-->

<div class='container'>

<h4>Number of processors:</h4>
<script>
function showVal(newVal){ 
    document.getElementById("slideVal").innerHTML = newVal;
}
</script> 

<div style="float:left;">Run on&nbsp;</div>
<div id="slideVal" style="float:left;">16</div>
<div style="float:left;">&nbsp;processor(s)</div><br>

<div style="height:30px;width:250px;float:left;">
1<input name="procs" type="range" min="1" max="31" step="1" value="16" oninput="showVal(this.value)"> 31</div></div>
<br>

</div>

<!--
#####################################
# Create DDBox for annotation files #
#####################################
-->

<div class='container'>

<?php

$afiles = scandir("$subdirectories/annotation_directory");

echo "<h4>Choose an annotation file:</h4>";
echo "<select name=\"afilename\">";
foreach ($afiles as $afile) 
{
  if(($afile != ".") and ($afile != ".."))
  { 
    echo "<option value=\"$afile\">$afile</option>";
  }
} 

?>
</select>
</div>

<!--
################################
# Create DDBox for fasta files #
################################
-->

<div class='container'>

<?php
$fafiles = scandir("$subdirectories/fasta_directory"); 

echo "<h4>Choose a fasta:</h4>";
echo "<select name=\"fafilename\">";
foreach ($fafiles as $fafile)
{
  if (($fafile != ".") and ($fafile != ".."))
  { 
    echo "<option value=\"$fafile\">$fafile</option>";
  }
} 
?>

</select>

</div>

<!--
#################
# Analysis Name #
#################
-->

<h4>Analysis Name:</h4> <input type="text" name="analysisname"><br>

<!--
#######################
# Annotation Selector #
#######################
-->

<h4> Annotation Type: </h4>
<input type="radio" name="annotationtype" value="ensembl" checked>Ensembl 
<input type="radio" name="annotationtype" value="ncbi">NCBI <br>

<br>
<br>
<!--
#################
# Captcha Stuff #
#################
-->

<?php
require_once('recaptchalib.php');
$publickey = "6LfK0PUSAAAAANftfso7uj8OdyarzxH0zvst0Tmf"; 
echo "Finally... Prove you're not a robot!";
echo recaptcha_get_html($publickey);
?>

<br>

<input type='hidden' name='submitted' id='submitted' value='1'/>

<!--
###########################
# Submit and Menu Buttons #
###########################
-->

<div class='container'>
<button id = "crunch" type="submit">fRNAkenstein, Crunch!</button>
</div>
<br> <br> <br>
</form>
<form action="index.html">
    <input align="bottom" type="submit" value="Return to Menu">
</form>
</td>

</td>

<!--
#######################
# Footer and clean-up #
#######################
-->

</tr>
</table>
</link></form>
<p align="right"><font size="1">- Created by Allen Hubbard and Wayne Treible at the University of Delaware - </font></p>
</fieldset>
</body>
