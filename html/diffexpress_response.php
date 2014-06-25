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

########################
# Captcha Verification #
########################

require_once('recaptchalib.php');
$privatekey = "6LfK0PUSAAAAAP_PlDXSa_jlAxw7g0W7z7qMvcNM ";
$resp = recaptcha_check_answer ($privatekey,
                        $_SERVER["REMOTE_ADDR"],
                        $_POST["recaptcha_challenge_field"],
                        $_POST["recaptcha_response_field"]);

if (!$resp->is_valid) {
echo "<script language=\"javascript\">";
echo "parent.location.reload();";
echo "</script>";

  die ("<h4> Error 15: reCaptcha not entered correctly</h4>");
} else {


############################################
# Some error checking redundancy on inputs #
############################################

if(empty($_POST['controlcondition'])){
	exit("<h4>Error 6: No control condition entered</h4>");
}
if(empty($_POST['controlfilename'])){
	exit("<h4>Error 7: No control libraries selected</h4>");
}
if(empty($_POST['expcondition'])){
	exit("<h4>Error 8: No experimental condition entered</h4>");
}
if(empty($_POST['expfilename'])){
	exit("<h4>Error 9: No experimental libraries selected</h4>");
}

if(empty($_POST['procs'])){
	exit("<h4>Error 10: Number of proccessors error</h4>");
}
if(empty($_POST['afilename'])){
	exit("<h4>Error 11: No annotation file selected</h4>");
}
if(empty($_POST['fafilename'])){
	exit("<h4>Error 12: No Fasta file selected</h4>");
}

if(empty($_POST['analysisname'])){
	exit("<h4>Error 13: No analysis name entered</h4>");
}
if(empty($_POST['annotationtype'])){
	exit("<h4>Error 14: No annotation type selected</h4>");
}

##################################
# Grab values from HTML elements #
##################################

$controlcondition = $_POST['controlcondition'];
$controllibs = $_POST['controlfilename'];
$expcondition = $_POST['expcondition'];
$explibs = $_POST['expfilename'];
$procs = htmlentities($_POST['procs']);
$anno = htmlentities($_POST['afilename']);
$fa = htmlentities($_POST['fafilename']);
$analysisname = $_POST['analysisname'];
$annotype = $_POST['annotationtype'];

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

# RUN COLIN SCRIPT #

######################
# R Programs Section #
######################

$rpath = "$analysispath/r_output";

###################
# BaySeqVariables #
################### 

$notNullModel1 = ""; 
$notNullModel2 = ""; 
$nullModel = "";

$controllist = rtrim(implode(',', $controllibs), ',');
$explist = rtrim(implode(',', $explibs), ',');

$controlcount = 0; 
$expcount = 0; 

#when we make the list, we want to add the comma after each entry except the last one
foreach ($controllibs as $library)  
{ 
	if ($controlcount < count($controllibs)-1) 
	{ 
		$notNullModel1 = $notNullModel1."1".","; 
	}
	else
	{ 
		$notNullModel1 = $notNullModel1."1"; 	      
	} 

	$controlcount += 1; 
} 

foreach ($explibs as $library) 
{ 
	if ($expcount < count($explibs)-1) 
	{ 
		$notNullModel2 = $notNullModel2."2".","; 
	} 
	else
	{ 
		$notNullModel2 = $notNullModel2."2"; 
	} 

	$expcount += 1; 
} 


########################
# Generate BaySeq info #
######################## 

$count = 0; 

foreach ($libs as $library) 
{ 
	if ($count < count($libs)-1) 
	{ 
		$nullModel = $nullModel . "1" . ",";    
	} 
	else
	{ 
		$nullModel = $nullModel . "1";   
	}    
	$count += 1; 
} 

####################
# DESeq2 Variables #
#################### 

$greparray = ""; 
$count = 0; 

foreach ($libs as $library) 
{ 
   
	if ($count == 0) 
	{ 
		$greparray = "(grep(\"$library\",list.files(\"$htseqpath\"),value=TRUE))"; 
	} 
	else
	{ 
		$greparray .= ",(grep(\"$library\",list.files(\"$htseqpath\"),value=TRUE))"; 
	} 

	$count += 1; 
} 


######################
# Generate R Command #
######################

$rcommand = "printf \"";
$rfilename = "command_$mytimeid.r";
$rcommandpath = "$analysispath/$rfilename";

$rcommand .= "source(\"http://bioconductor.org/biocLite.R\") \n"; 
$rcommand .= "biocLite() \n"; 
$rcommand .= "biocLite(\"baySeq\") \n"; 
$rcommand .= "biocLite(\"DESeq2\") \n"; 
$rcommand .= "biocLite(\"edgeR\") \n"; 
$rcommand .= "library(DESeq2) \n"; 
$rcommand .= "library(baySeq) \n"; 
$rcommand .= "library(edgeR) \n"; 

###################
# BaySeq Commands #
###################
 
$rcommand .= "all = read.delim(\"$htseqpath/count_matrix.txt\", header=TRUE, sep=\"\\t\")\n"; 
$rcommand .= "lib <- c($controllist,$explist) \n"; 
$rcommand .= "replicates <- c($notNullModel1,$notNullModel2) \n"; 
$rcommand .= "groups <- list(NDE = c($nullModel), DE = c($notNullModel1,$notNullModel2))\n"; 
$rcommand .= "cname <- all[,1] \n"; 
$rcommand .= "all <- all[,-1] \n"; 
$rcommand .= "all = as.matrix(all) \n"; 
$rcommand .= "CD <- new(\"countData\", data = all, replicates = replicates, libsizes = as.integer(lib), groups = groups) \n"; 
$rcommand .= "library(parallel) \n"; 
$rcommand .= "CD@annotation <- as.data.frame(cname) \n"; 
$rcommand .= "cl <- NULL \n"; 
$rcommand .= "CDP.NBML <- getPriors.NB(CD, samplesize = 1000, estimation = \"QL\", cl = cl) \n"; 
$rcommand .= "CDPost.NBML <- getLikelihoods.NB(CDP.NBML, pET = 'BIC', cl = cl) \n"; 
$rcommand .= "CDPost.NBML@estProps \n";  
$rcommand .= "topCounts(CDPost.NBML, group=2) \n"; 
$rcommand .= "NBML.TPs <- getTPs(CDPost.NBML, group=2, TPs = 1:100) \n"; 
$rcommand .= "topCounts(CDPost.NBML, group=2)\n"; 
$rcommand .= "blah <- topCounts(CDPost.NBML,group=\"DE\",FDR=1) \n"; 
$rcommand .= "write.csv(blah, file=\"$rpath/bayseq_de_analyzed_dataset_$analysisname.csv\") \n"; 

###################
# DESEQ2 Commands #
###################
 
$rcommand .= "directory <- \"$htseqpath\" \n"; 
$rcommand .= "sampleFiles <- c($greparray) \n";  #CREATE THE DESEQ2 object 
$rcommand .= "sampleCondition <- c(rep(\"C\",$controlcount),rep(\"T\",$expcount)) \n"; 
$rcommand .= "sampleTable<- data.frame(sampleName=sampleFiles, fileName=sampleFiles, condition=sampleCondition) \n"; 
$rcommand .= "ddsHTSeq<-DESeqDataSetFromHTSeqCount(sampleTable=sampleTable, directory=directory, design=~condition) \n"; 
$rcommand .= "dds<-DESeq(ddsHTSeq) \n"; 
$rcommand .= "res<-results(dds) \n"; 
$rcommand .= "res<-res[order(res\$padj),] \n"; 
$rcommand .= "head(res) \n"; 
$rcommand .= "write.table (as.data.frame(res), file=\"$rpath/results_deseq2.txt\") \n"; 

##################
# EdgeR Commands #
##################

$rcommand .= "library(edgeR) \n"; 
$rcommand .= "group <- c($notNullModel1,$notNullModel2) \n"; 
$rcommand .= "y <- DGEList(counts=all, group= group) \n"; 
#make the R object from the list of counts and the annotation                               
#vector from previous analyses 
$rcommand .= "dge <- DGEList(counts=y, group=group, genes = cname ) \n";  
$rcommand .= "dge <- calcNormFactors(dge) \n"; 
$rcommand .= "dge = estimateCommonDisp(dge) \n"; 
$rcommand .= "de.com = exactTest(dge)   \n"; 
$rcommand .= "topTags(de.com)  \n"; 
$rcommand .= "goodList = topTags(de.com, n=\"good\") \n"; 
$rcommand .= "write.table (as.data.frame(goodList), file=\"$rpath/edgeR.txt\") \n"; 


# FIX THIS LATER DAWG #
# $rcommand .= "\" > /rtestfile.txt";
# file_put_contents("/var/www/rtest1.sh", $rcommand);

$commands .= $cmcommand.$cdcommand.$htseqcommand;

#echo nl2br($rcommand);
echo $commands;# for testing
#echo "$cmcommand<br>";
#echo "$cdcommand"; 


}
?>


</body>
</html>
