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

############################################
# Some error checking redundancy on inputs #
############################################

if(empty($_GET['controlcondition'])){
	exit("<h4>Error 6: No control condition entered</h4>");
}
if(empty($_GET['controlfilename'])){
	exit("<h4>Error 7: No control libraries selected</h4>");
}
if(empty($_GET['expcondition'])){
	exit("<h4>Error 8: No experimental condition entered</h4>");
}
if(empty($_GET['expfilename'])){
	exit("<h4>Error 9: No experimental libraries selected</h4>");
}

if(empty($_GET['procs'])){
	exit("<h4>Error 10: Number of proccessors error</h4>");
}
if(empty($_GET['afilename'])){
	exit("<h4>Error 11: No annotation file selected</h4>");
}
if(empty($_GET['fafilename'])){
	exit("<h4>Error 12: No Fasta file selected</h4>");
}

if(empty($_GET['analysisname'])){
	exit("<h4>Error 13: No analysis name entered</h4>");
}
if(empty($_GET['annotationtype'])){
	exit("<h4>Error 14: No annotation type selected</h4>");
}

##################################
# Grab values from HTML elements #
##################################

$controlcondition = $_GET['controlcondition'];
$controllibs = $_GET['controlfilename'];
$expcondition = $_GET['expcondition'];
$explibs = $_GET['expfilename'];
$procs = htmlentities($_GET['procs']);
$anno = htmlentities($_GET['afilename']);
$fa = htmlentities($_GET['fafilename']);
$analysisname = $_GET['analysisname'];
$annotype = $_GET['annotationtype'];

########################
# Printing information #
########################

echo "<body >";
echo "<div id='result_div'>";
echo "<h4>Crunching library with data:</h4>";
	
echo "<p >";
echo "Control condition: $controlcondition";
echo "</p>";

echo "<p>";
echo "Control library file(s) selected: <br>";
foreach ($controllibs as $controllib){
	echo $controllib."<br>";
}
echo "</p>";

echo "<p >";
echo "Experimental condition: $controlcondition";
echo "</p>";

echo "<p>";
echo "Experimental library file(s) selected: <br>";
foreach ($explibs as $explib){
	echo $explib."<br>";
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

echo "<p >";
echo "Analysis name: $analysisname";
echo "</p>";

echo "<p >";
echo "Annotation type: $annotype";
echo "</p>";
	
echo "<p>";
echo "<b>NOTE:</b><br>Running the pipeline will take a long time!";
echo "</p>";
	
echo "</div>";

##########################################
# Set Analysis Path and THCL Output Path #
##########################################

$analysispath = "$subdirectories/temp_output/analysis_$analysisname";
$thclpath = "$subdirectories/thcl_output";

#################################
# Log initialization and run ID #
#################################

# Generate a unique ID based on the time and echo it
$mytimeid = date('his.m-d-Y');
echo "<b>Your run ID is: </b> $mytimeid<br><br>";

# Create log path and initialize it
$logfile = "$subdirectories/logs/$mytimeid.diffexp.log";

#######################
# Initialize Commands #
#######################

$commands = "";
$commands = "mkdir -p $analysispath\n";

#############################
# Merge ctrl and exp arrays #
#############################

$libs = array_merge($explibs, $controllibs);

############################
# Build Cuffmerge Manifest #
############################

$manifest = "";
$manifestpath = "$analysispath/manifest.txt";

foreach($libs as $lib)
{
  $manifest = $manifest."$thclpath/$lib/cufflinks_out/transcripts.gtf\n";
}

#file_put_contents($manifestpath, $manifest, LOCK_EX);

##############################
# Annotation and fasta paths #
##############################

$annopath = "$subdirectories/annotation_directory/$anno";
$fapath = "$subdirectories/fasta_directory/$fa";

###########################
# Build CuffMerge Command #
###########################

$cmoutputpath = "$analysispath/cuffmerge_output";
$cmcommand = "mkdir -p $cmoutputpath\ncuffmerge -p $procs -g $annopath -o $cmoutputpath -s $fapath $manifestpath\n";

##########################
# Build CuffDiff Command #
##########################

$cdoutputpath = "$analysispath/cuffdiff_output";

$bampaths = "";

$count = 0;
foreach($controllibs as $controllib)
{
  $bampaths = $bampaths."$thclpath/$controllib/tophat_out/accepted_hits.bam";
  $count = $count + 1;
  if ($count < count($controllibs))
  {
    $bampaths = $bampaths.",";
  }
}
$bampaths = $bampaths." ";

$count = 0;
foreach($explibs as $explib)
{
  $bampaths = $bampaths."$thclpath/$explib/tophat_out/accepted_hits.bam";
  $count = $count + 1;
  if ($count < count($explibs))
  {
    $bampaths = $bampaths.",";
  }
}

$cdcommand = "mkdir -p $cdoutputpath\ncuffdiff -p $procs -o $cdoutputpath -l $controlcondition,$expcondition $cmoutputpath $bampaths\n";

######################
# HTSeqCount Command #
######################

$sampath = "$analysispath/sam_output";
$htseqpath = "$analysispath/htseq_output";

$htseqcommand = "mkdir -p $sampath\nmkdir -p $htseqpath\n";
    
foreach($libs as $lib)
{
	preg_match("/library_(.*)/",$lib,$match);
	$library = $match[1];
	
	$htseqcommand = $htseqcommand."samtools view -h -o $sampath/$library.sam $thclpath/library_$library/tophat_out/accepted_hits.bam\n";

	if ($annotype == "ncbi") 
	{ 
		$htseqcommand = $htseqcommand."htseq-count -t gene -i gene $analysispath/$library.sam $annopath > $htseqpath/$library.counts\n";

	} 
	else if ($annotype == "ensembl") 
	{ 
		$htseqcommand = $htseqcommand."htseq-count -t gene -i Name $analysispath/$library.sam $annopath > $htseqpath/$library.counts\n"; 
	} 
}


######################
# Build count matrix #
######################

# RUN COLIN SCRIPT

######################
# R Programs Section #
######################




$commands = $commands.$cmcommand.$cdcommand.$htseqcommand;


echo $commands;
# echo commands for testing
#echo "$cmcommand<br>";
#echo "$cdcommand"; 

?>


</body>
</html>
