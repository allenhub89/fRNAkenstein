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


session_start();

if(empty($_SESSION['user_name']))
{
  header('Location: index.php');
}


############################################
# Some error checking redundancy on inputs #
############################################

if(empty(strip_tags (htmlspecialchars( escapeshellcmd($_GET['fqfilename']))))){
	exit("<h4>Error 1: No Fastq file selected</h4>");
}
if(empty(strip_tags (htmlspecialchars( escapeshellcmd($_GET['procs']))))){
	exit("<h4>Error 2: Number of proccessors error</h4>");
}
if(empty(strip_tags (htmlspecialchars( escapeshellcmd($_GET['afilename']))))){
	exit("<h4>Error 3: No annotation file selected</h4>");
}
if(empty(strip_tags (htmlspecialchars( escapeshellcmd($_GET['fafilename']))))){
	exit("<h4>Error 4: No Fasta file selected</h4>");
}

##################################
# Grab values from HTML elements #
##################################

$fqarray = strip_tags (htmlspecialchars( escapeshellcmd($_GET['fqfilename'])));
$procs = strip_tags (htmlspecialchars( escapeshellcmd(htmlentities($_GET['procs']))));
$anno = strip_tags (htmlspecialchars( escapeshellcmd(htmlentities($_GET['afilename']))));
$fa = strip_tags (htmlspecialchars( escapeshellcmd(htmlentities($_GET['fafilename']))));

########################
# Printing information #
########################

echo "<body >";
echo "<div id='result_div'>";
echo "<h4>Crunching library with data:</h4>";
	
echo "<p>";
echo "Library file(s) selected: <br>";
foreach ($fqarray as $fqfile){
	echo $fqfile."<br>";
}
echo "</p>";

echo "<p >";
echo "# Procs: $procs";
echo "</p>";

echo "<p >";
echo "Annotation file: $anno";
echo "</p>";

echo "<p >";
echo "Fasta file: $fa";
echo "</p>";
	
echo "<p>";
echo "<b>NOTE:</b><br>Running the pipeline will take a long time; <br>results will be emailed automatically upon conclusion!";
echo "</p>";
	
echo "</div>";

##########################
# Begin front end script #
##########################

# Initialize output command string
$outputcommands = "";



# Generate a unique ID based on the time and echo it
$mytimeid = date('his.m-d-Y');
echo "<b>Your run ID is: </b> $mytimeid<br><br>";

# Create log path and initialize it
$logfile = "$subdirectories/logs/$mytimeid.thcl.log";
$logoutput = "Bash commands...\n";

# Temp output path
$temppath = "$subdirectories/temp_output";

# For every library selected:
foreach($fqarray as $fqoriginal) {


	# Initialize moveandgunzip command
	$movecommand = "";

	# Split for double stranded
	$fqdoublestranded = explode("&", $fqoriginal);
	# don't like this but it needs to be done for clarity below
	$fq = $fqdoublestranded[0];
	
	# Check if fastq file is zipped
	if (preg_match("/\.gz/", $fq)!=1 ){
		exit("Error 5: Fastq file not gzipped (.gz)");
	} else {

		# If double stranded, path is equal to both paths delimited by a space
		if (count($fqdoublestranded)==2) {
			$fqpath = "$subdirectories/fastq_to_be_crunched/$fqdoublestranded[0]";
			$fqpath2strand = "$subdirectories/fastq_to_be_crunched/$fqdoublestranded[1]";

			if(preg_match("/\.gz/", $fqpath)==1 and preg_match("/\.gz/", $fqpath2strand)==1){
				$movecommand .= "mv $fqpath $temppath/$fqdoublestranded[0]\n";
				$movecommand .= "mv $fqpath2strand $temppath/$fqdoublestranded[1]\n";
				$movecommand .= "gunzip $temppath/*.gz\n";;
				$fq = preg_replace("/.gz/","",$fq);
				$fq2 = preg_replace("/.gz/","",$fqdoublestranded[1]);	
				$fqpath = "$temppath/$fq";
				$fqpath2strand = "$temppath/$fq2";
				$fqpath = $fqpath." ".$fqpath2strand;
			}
		}
		# Otherwise, it's just equal to the one fastq filepath
		else{
			$fqpath = "$subdirectories/fastq_to_be_crunched/$fq";

			if(preg_match("/\.gz/", $fqpath)==1)
			{
				$movecommand .= "mv $fqpath $temppath/\n";
				$movecommand .= "gunzip $temppath/$fq\n";
				$fq = preg_replace("/.gz/","",$fq);
				$fqpath = "$temppath/$fq";

			}
		}
		
		# Generate other file paths for annotation and fasta files
		$annopath = "$subdirectories/annotation_directory/$anno";
		$fapath = "$subdirectories/fasta_directory/$fa/$fa";

		# Parse library number
		$library = preg_replace("/(_[a-zA-Z0-9]*)+(\.[a-zA-Z0-9]*)+/","",$fq);

		# Generate location for output files
		$thoutputfile = "$temppath/library_$library/tophat_out";
		$cloutputfile = "$temppath/library_$library/cufflinks_out";

		# Generate commands for TH and CL
		$thcommand = "tophat -p $procs -o $thoutputfile $fapath $fqpath";
		$clcommand = "cufflinks -p $procs -g $annopath -o $cloutputfile $thoutputfile/accepted_hits.bam";
	
		# Generate mkdir commands for new directories
		# -p option prevents errors with pre-existing folders
		$makedirs = "mkdir -p $temppath/library_$library && mkdir -p $cloutputfile && mkdir -p $thoutputfile";

		# Append library commands to the output command string
		$singleoutputcommand = "$makedirs\n$thcommand >> $logfile 2>&1 && $clcommand >> $logfile 2>&1\n";
		$outputcommands = $outputcommands.$movecommand.$singleoutputcommand;
	
		# Build log output
		$logoutput = $logoutput."Fastq file: $fqoriginal\n"."Command generated: ".$singleoutputcommand;
	}
}

# Create bash file output directory
$bashfile = "$subdirectories/bash_scripts/run_$mytimeid.thcl.sh";

# Append to log output (TH and CL will redirect stderr to log file)
$logoutput = $logoutput."THCL output...\n";

# Move temp files to output directory only after the library has been crunched	
# and add to the commands file	
$thclpath = "$subdirectories/thcl_output";
$mvcommand = "mv -f $temppath/library_* $thclpath/ >> $logfile 2>&1";
$outputcommands = $outputcommands.$mvcommand;

# Write files
file_put_contents($logfile, $logoutput);
file_put_contents($bashfile, $outputcommands, LOCK_EX);

# Test output of commands
# echo $outputcommands;

# Execute bash file 
system("bash $bashfile");


?>

<!--
#########################
# fRNAkenstein Reloader #
#########################
-->

<script language="javascript">
function reloader()
{	
	/* Reload window */
	parent.location.reload();
	/* Set iFrame to empty */
	window.location.assign("about:blank");

}
</script>

<input type="button" value="Run fRNAkenstein again!" onClick="return reloader(this);">


</body>
</html>
