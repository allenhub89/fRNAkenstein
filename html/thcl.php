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
fRNAkenstein:"Beware; for I am fearless, and therefore powerful."
</title>
<link rel="STYLESHEET" type="text/css" href="css_dir/style.css">
<link rel="icon" type="image/ico" href="images/favicon.ico"/>
</head>
<body>

<!--
############################
# Beginning of submit form #
############################
-->
<fieldset >
<legend>
<h3>
<!img src="images/favicon.png" alt="fRNAk" width="24" height="24"> 
Tophat & Cufflinks: "Beware; for I am fearless, and therefore powerful." 
<!img src="images/favicon.png" alt="fRNAk" width="24" height="24">
</h3>
</legend>
<form id='submitform' onsubmit="return valthisform(this);" action='/thcl_response.php' method='get' target='formresponse'>


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
	var checkboxs=document.getElementsByName("fqfilename[]");
	var okay=false;
	for(var i=0,l=checkboxs.length;i<l;i++)
	{
		if(checkboxs[i].checked)
		{
	    okay=true;
		}
	}
	if(okay){
		document.getElementById('crunch').disabled = 1
		alert("Submitted processing request!");
	}
	else alert("Please select a library!");
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
################################################
# Create Checkboxes for fastq files (lib nums) #
################################################
-->

<div class='container'>

<?php
$fqfiles = scandir("$subdirectories/fastq_to_be_crunched");

echo "<h4>Choose library number(s):</h4>";
foreach($fqfiles as $fqfile)
{
  # Modifying arrays while 'foreach' iterating is broken in php
  # -> It buffers the array at the foreach call and iterates over 
	# -> potentially old or modified data (bad, php!)
  # This double checks that the element is in the new fqfiles array 
	# to fix this minor annoying problem...
  if (($key = array_search($fqfile, $fqfiles)) !== false) 
	{
	  $doublestranded = 0;
	  if ($fqfile !== "." and $fqfile !== "..")
	  { 
	    $librarynum = "";
	    $libpattern = "/^s*(\d*).*/";
	    preg_match($libpattern, $fqfile, $matches);
	    $librarynum = $matches[1];
	  
	    foreach ($fqfiles as $fqfile2)
	    {
				if ($fqfile != "." and $fqfile != ".." and $fqfile2 != "." and $fqfile2 != "..")
	  	  { 
		    	$librarynum2 = "";
		    	$libpattern = "/^s*(\d*).*/";
					preg_match($libpattern, $fqfile2, $matches2);
		    	$librarynum2 = $matches2[1];
	
				  if(($librarynum2 == $librarynum) and ($fqfile !== $fqfile2))
				  {
						# Remove double stranded results from list
						if (($key = array_search($fqfile, $fqfiles)) !== false) 
						{
							$key2 = array_search($fqfile2, $fqfiles);
				 		  unset($fqfiles[$key]);
							unset($fqfiles[$key2]);
						}	      	
					  $doublestranded = 1;

				    echo "<input type=\"checkbox\" name=\"fqfilename[]\" value=\"$fqfile&$fqfile2\">$librarynum (double stranded)<br>";

				  } 
		  	}
			}

	    if ($doublestranded == 0)
	    {
				echo "<input type=\"checkbox\" name=\"fqfilename[]\" value=\"$fqfile\">$librarynum<br>";
	    }
	  }
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
############################
# Submit and Queue Buttons #
############################
-->

<div class='container'>
<button id = "crunch" type="submit">fRNAkenstein, Crunch!</button>
</div>
<br> <br> <br> 
</form>
<form action="index.html">
    <input type="submit" value="Return to Menu">
</form>
</td>

<!--
#######################
# iFrame for Response #
#######################
-->

<td valign="top" align='left' style="padding-left:0px;align:left">
<br>
<iframe name='formresponse' width='300px' height='500px' frameborder='0'>
</iframe>

<!--
#######################
# Footer and clean-up #
#######################
-->

</td>
</tr>
</table>
</link></form></fieldset>
</body>
<p align="right"><font size="1">- Created by Allen Hubbard and Wayne Treible at the University of Delaware - </font></p>


