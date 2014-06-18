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

##################################
# Grab values from HTML elements #
##################################

$fqarray = $_GET['fqfilename'];
$procs = htmlentities($_GET['procs']);
$anno = htmlentities($_GET['afilename']);
$fa = htmlentities($_GET['fafilename']);

############################################
# Some error checking redundancy on inputs #
############################################

if($fqarray == NULL){
	exit("<h4>Error 1: No Fastq file selected</h4>");
}
if($procs == NULL){
	exit("<h4>Error 2: Number of proccessors error</h4>");
}
if($anno == NULL){
	exit("<h4>Error 3: No annotation file selected</h4>");
}
if($fa == NULL){
	exit("<h4>Error 4: No Fasta file selected</h4>");
}

########################
# Printing information #
########################

echo "<body >";
echo "<div id='result_div'>";
echo "<h4>Crunching library with data:</h4>";
	
echo "<p>";
echo "Library file(s) selected: ";
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
$mytimeid = date('his-m-d-Y');
echo "<b>Your run ID is: </b> $mytimeid<br><br>";

# Create log path and initialize it
$logfile = "$subdirectories/logs/$mytimeid.log";
$logoutput = "Bash commands...\n";

# For every library selected:
foreach($fqarray as $fqoriginal) {

	# Split for double stranded
	$fqdoublestranded = explode("&", $fqoriginal);
	# don't like this but it needs to be done
	$fq = $fqdoublestranded[0];
	
	# Check if fastq file is zipped
	if (preg_match("/\.gz/", $fq)!=1 ){
		exit("Error 5: Fastq file not gzipped (.gz)");
	} else {
		
		$unzippedpath = "$subdirectories/unzipped_fastq_directory";

		# If double stranded, path is equal to both paths delimited by a space
		if (count($fqdoublestranded)==2) {
			$fqpath = "$subdirectories/fastq_to_be_crunched/$fqdoublestranded[0]";
			$fqpath2strand = "$subdirectories/fastq_to_be_crunched/$fqdoublestranded[1]";

			if(preg_match("/\.gz/", $fqpath)==1 and preg_match("/\.gz/", $fqpath2strand)==1){
				system("mv $fqpath $unzippedpath/$fqdoublestranded[0]");
				system("mv $fqpath2strand $unzippedpath/$fqdoublestranded[1]");
				system("gunzip $unzippedpath/*.gz");
				$fqpath = preg_replace("/.gz/","",$fq);
				$fqpath2strand = preg_replace("/.gz/","",$fq);
				$fqpath = $fqpath." ".$fqpath2strand;
			}
		}
		# Otherwise, it's just equal to the one fastq filepath
		else{
			$fqpath = "$subdirectories/fastq_to_be_crunched/$fq";

			if(preg_match("/\.gz/", $fqpath)==1)
			{
				system("mv $fqpath $unzippedpath/");
				system("gunzip $unzippedpath/$fq");
				$fq = preg_replace("/.gz/","",$fq);
				$fqpath = "$unzippedpath/$fq";

			}
		}
		
		# Generate other file paths for annotation and fasta files
		$annopath = "$subdirectories/annotation_directory/$anno";
		$fapath = "$subdirectories/fasta_directory/$fa/$fa";

		# Parse library number
		$library = preg_replace("/(_[a-zA-Z0-9]*)+(\.[a-zA-Z0-9]*)+/","",$fq);

		# Generate location for output files
		$thoutputfile = "$subdirectories/output_directory/library_$library/tophat_out";
		$cloutputfile = "$subdirectories/output_directory/library_$library";

		# Generate commands for TH and CL
		$thcommand = "tophat -p $procs -o $thoutputfile $fapath $fqpath";
		$clcommand = "cufflinks -p $procs -g $annopath -o $cloutputfile $thoutputfile/accepted_hits.bam";
	
		# Generate mkdir commands for new directories
		# -p option prevents errors with pre-existing folders
		$makedirs = "mkdir -p $cloutputfile && mkdir -p $thoutputfile";

		# Append library commands to the output command string
		$singleoutputcommand = "$makedirs && $thcommand >> $logfile 2>&1 && $clcommand >> $logfile 2>&1\n";
		$outputcommands = $outputcommands.$singleoutputcommand;
	
		# Build log output
		$logoutput = $logoutput."Fastq file: $fqoriginal\n"."Command generated: ".$singleoutputcommand;
	}
}

# Create bash file output directory
$bashfile = "$subdirectories/bash_scripts/run_$mytimeid.sh";

# Append to log output (TH and CL will redirect stderr to log file)
$logoutput = $logoutput."Tophat & Cufflinks output...\n";

# Write files
file_put_contents($logfile, $logoutput);
file_put_contents($bashfile, $outputcommands, LOCK_EX);

# Execute bash file 
#system("bash $bashfile");


?>

<!--
#########################
# fRNAkenstein Reloader #
#########################
-->

<script language="javascript">
function reloader()
{	
	parent.location.reload();
	window.location.assign("about:blank");

}
</script>

<input type="button" value="Run fRNAkenstein again!" onClick="return reloader(this);">


</body>
</html>
