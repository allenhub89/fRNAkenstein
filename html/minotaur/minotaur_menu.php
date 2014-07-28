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
session_start();
if(empty($_SESSION['user_name']) && !($_SESSION['user_is_logged_in']))
{
  #header('Location: index.php');
}
?>
<!--
##########
# Header #
##########
-->

<head>
<title>
MInotauR:"Run to the passage while he storms, 'tis well that thou descend.."
</title>
<link rel="STYLESHEET" type="text/css" href="css_dir/style.css">
<link rel="STYLESHEET" type="text/css" href="css_dir/buttonStyle.css">
<link rel="icon" type="image/ico" href="images/favicon.ico"/>
</head>
<body>
<center>
<!--
###########################
# Formatting Box & Legend #
###########################
-->
<style type="text/css">
    .fieldset-auto-width {
         display: inline-block;
    }
</style>
<div>
<!fieldset class="fieldset-auto-width">

<table style="margin: 0px;">
<tr>
<th colspan="3" valign="middle" bgcolor="" style="border-bottom: 1px solid #000;padding-top:24px;padding-bottom:24px;padding-left:24px;padding-right:12px;width:500px;height=160px;">
<img src="images/minotaur_banner.png" alt="MInotauR" width="550" style="" > </td>

</th>
</tr>

<tr>
<td valign="middle"  style="padding-top:24px;padding-left:8px;width:50px;">
<a href="instructions.php" class="minbutton">Instructions</a>

</td>
<td valign="middle"  style="padding-top:12px;padding-left:8px;width:400px">
<b>Step 1:</b> Learn about MInotauR's included tools and 
how to use the front-end interface step-by-step.
</td>
</tr>

<tr>
<td valign="middle"  style="padding-top:12px;padding-left:8px;width:50px;">
<a href="mapcount.php" class="minbutton">MapCount</a>

</td>
<td valign="middle"  style="padding-top:12px;padding-left:8px;width:400px">
<b>Step 2:</b> Align RNA sequencing reads to the reference file 
using Tophat and Cufflinks from the Tuxedo Suite.
</td>
</tr>

<tr>
<td valign="middle"  style="padding-top:12px;padding-left:8px;width:50px;">
<a href="diffexpress.php" class="minbutton">DiffExpress</a>
</td>
<td valign="middle"  style="padding-top:12px;padding-left:8px;width:400px">
<b>Step 3:</b> Calculate differential expression levels using an array of tools including 
Cuffdiff, EdgeR, DESeq2, and Bayseq, then combine the results.
</td>
</tr>

<tr>
<td valign="middle"  style="padding-top:12px;padding-left:8px;width:50px;">
<a href="http://bigbird.anr.udel.edu/~sunliang/pathway/cyto.php" class="minbutton">Visualize Data</a>

<td valign="middle"  style="padding-top:12px;padding-left:8px;width:400px">
<b>Step 4:</b> Visualize differential expression 
data using a variety of visualization tools. 
</td>

<tr>
<td valign="middle"  style="padding-top:12px;padding-left:8px;width:50px;">
<a href="status.php" class="minbutton">Status</a>

<td valign="middle"  style="padding-top:12px;padding-left:8px;width:400px">
View the various output and error logs of your data runs in real-time using the run ID provided in each tool.
</td>
</tr>

<tr>
<td valign="middle"  style="padding-top:12px;padding-left:8px;width:50px;">
<a href="contact.html" class="minbutton">About & Contact</a>

<td valign="middle"  style="padding-top:12px;padding-left:8px;width:400px">
Contact information for the Fable team and references to the tools used.
</td>
</tr>

</table>

<!--
##########
# Footer #
##########
-->

</link>

</fieldset>
<br><br>
<img src="../images/chicken.jpg" alt="SchmidtLab" width="160" height="125" > </td>
<img src="../images/USDA.jpg" alt="USDA" width="266" height="125"> 
<img src="../images/NSF.jpg" alt="NSF" width="125" height="125"> <br>
<p align="center" ><font size="1">- NSF award: 1147029 :: USDA-NIFA-AFRI: 2011-67003-30228 - </font></p><br>
<p align="center" ><font size="1">- Created by Allen Hubbard and Wayne Treible at the University of Delaware - </font></p>
</div>
</body>



