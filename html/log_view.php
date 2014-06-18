<!--
######################################
# fRNAkenstein                       #
#   by Allen Hubbard & Wayne Treible #
#                                    #
# A front-end interface for the      #
# tuxedo pipeline including Tophat,  #
# Cufflinks, and Cuffdiff.           #
#                                    #
# Version 0.10 Updated 6/18/2014     #
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
Welcome to fRNAkenstein: "Beware; for I am fearless, and therefore powerful." 
</h3>
</legend>
<form id='submitform' action='/log_response.php' method='get' target='formresponse'>


<input type='hidden' name='submitted' id='submitted' value='1'/>

<!--
################################
# Beginning of alignment table #
################################
-->

<table height="90%" style="margin: 0px;">
<tr>
<td valign="top" >

<!--
#####################################
# Create DDBox for annotation files #
#####################################
-->

<div class='container'>

<?php

$logfiles = scandir("$subdirectories/logs");

echo "<h4>Select your log by run ID:</h4>";
echo "<select name=\"log\">";
foreach ($logfiles as $logfile) 
{
  if(($logfile != ".") and ($logfile != ".."))
  { 
    echo "<option value=\"$logfile\">$logfile</option>";
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
<button id = "crunch" type="submit">View Log!</button>
</div>

</form>

<!--
<form id='submitform2' action='/log_response.php' method='get' target='formresponse'>
  <h4>Search by run ID:</h4>
  <input type="text" name="fname"><br>
  <input type="submit" value="View Log!">
</form>
-->

<br> <br> <br>
<form action="index.html">
    <input type="submit" value="Return to Menu">
</form>
</td>

<!--
#######################
# iFrame for Response #
#######################
-->

<td valign="top" align='left' style="padding-left:20px;align:left">
<br>
<iframe name='formresponse' width='500px' height='500px' frameborder='0'>
</iframe>

<!--
#######################
# Footer and clean-up #
#######################
-->

</td>
</tr>
</table>
</link></fieldset>
</body>
<p align="right"><font size="1">- Created by Allen Hubbard and Wayne Treible at the University of Delaware - </font></p>


