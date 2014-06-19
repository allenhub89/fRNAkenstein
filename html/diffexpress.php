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
#   --unzipped_fastq_directory/    #
#   --bash_scripts/                #
#   --output_directory/            #
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
<form id='submitform' onsubmit="return valthisform(this);" action='about:blank' method='get' target='formresponse'>


<input type='hidden' name='submitted' id='submitted' value='1'/>

<!--
################################
# Beginning of alignment table #
################################
-->

<table height="90%" style="margin: 0px;">

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
		document.getElementById('crunch').disabled = 1
		alert("Running DiffExpress on Data!");
	}
	else alert("Please select both libraries!");
	return okay;
}

</script>

<!--
#############################
# Row for form and response #
#############################
-->

<tr style="padding:0px; margin:0px;">
<td valign="top" style="padding-top:12px;padding-left:8px;width:300px">

<!--
##################################################
# Create Checkboxes for library files (lib nums) #
##################################################
-->

<div class='container'>

<?php
$controllibs = scandir("$subdirectories/fastq_to_be_crunched");

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
    $libpattern = "/^s*(\d*).*/";
    preg_match($libpattern, $library, $matches);
    $librarynum = $matches[1];
    echo "<input type=\"checkbox\" name=\"controlfilename[]\" value=\"$library\">$librarynum<br>";
  }
} 

echo "</select>";

?>

</div>

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

<br>

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

<!--
##################################################
# Create Checkboxes for library files (lib nums) #
##################################################
-->
<td valign="top" style="padding-top:12px;padding-left:8px;width:300px">
<div class='container'>

<?php
$explibs = scandir("$subdirectories/fastq_to_be_crunched");

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
    $libpattern = "/^s*(\d*).*/";
    preg_match($libpattern, $explibrary, $matches);
    $librarynum = $matches[1];
    echo "<input type=\"checkbox\" name=\"expfilename[]\" value=\"$explibrary\">$librarynum<br>";
  }
} 

echo "</select>";

?>

</div>

</td>

<!--
#######################
# iFrame for Response #
#######################
-->

<td valign="top" style="padding-left:0px;align:left">
<br>
<iframe name='formresponse' style="border: outset;height: 95% ; background-color:#d0eace" width='500px' frameborder='0'>
</iframe>

<!--
#######################
# Footer and clean-up #
#######################
-->

</td>
</tr>
</table>
</link></form>
<p align="right"><font size="1">- Created by Allen Hubbard and Wayne Treible at the University of Delaware - </font></p>
</fieldset>
</body>
