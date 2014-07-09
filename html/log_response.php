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

$logdirectory = "$subdirectories/logs";

if(empty($_GET['log'])){
	exit("<h4>Error 6: No log file selected</h4>");
}

$logfile = strip_tags (htmlspecialchars( escapeshellcmd($_GET['log'])));

$logfilepath = "$logdirectory/$logfile";

$fh = fopen("$logfilepath", 'r');
$pageText = fread($fh, 1000000);
$pageText = preg_replace("/^Bash commands\.\.\./", "<b>Displaying subset of log ($logfile):</b>\n", $pageText);

$pageText = preg_replace('/(Command generated:)(.*)\n/',"\nCommands:\n", $pageText);

$pageText .= "\n <b>=== END OF LOG SUBSET ===</b>";

#converts newlines to <br>
echo nl2br($pageText);


?>
