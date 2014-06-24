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


#########BaySeqVariables################## 
$notNullModel1 = ""; 
$notNullModel2 = ""; 
$nullModel = "";

$controllist = rtrim(implode(',', $controllibs), ',');
$explist = rtrim(implode(',', $explibs), ',');

$controlcount = 0; 
$expcount = 0; 

foreach ($controllibs as $library) #when we make the list, we want to add the comma after each entry except the last one 
{ 
	if ($list1Count < count($controllibs)) 
	{ 
		$notNullModel1 = $notNullModel1."1".","; 
	}
	else #when we make the list, we want to add the comma after each entry except the last one 
	{ 
		$notNullModel1 = $notNullModel1."1"; 	      
	} 

	$controlcount += 1; 
} 

foreach ($explibs as $library) 
{ 
	if ($list2Count < count($explibs)) 
	{ 
		$notNullModel2 = $notNullModel2."2".","; 
	} 
	else
	{ 
		$notNullModel2 = $notNullModel2."2"; 
	} 

	$expcount += 1; 
} 


#####GenerateBaySeqVariableValues########### 

$count = 0; 

foreach ($explibs as $library) 
{ 
	if ($count < count($libs)) 
	{ 
		$nullModel = $nullModel . "1" . ",";    
	} 
	else
	{ 
		$nullModel = $nullModel . "1";   
	}    
	$count += 1; 
} 
####################################### 

#############DESeq2 Variables ###################### 

$greparray = ""; 

####################################################

$count = 0; 
foreach ($explibs as $library) 
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

$rcommand = "";
$rfilename = "command_$mytimeid.r";
$rcommandpath = "$analysispath/$rfilename";

open(OUT, ">>$rcommandpath"); 

rcommand .= "source(\"http://bioconductor.org/biocLite.R\") \n"; 
rcommand .= "biocLite() \n"; 
rcommand .= "biocLite(\"baySeq\") \n"; 
rcommand .= "biocLite(\"DESeq2\") \n"; 
rcommand .= "biocLite(\"edgeR\") \n"; 
rcommand .= "library(DESeq2) \n"; 
rcommand .= "library(baySeq) \n"; 
rcommand .= "library(edgeR) \n"; 

######################BaySeq Commands############################# 
print OUT "all = read.delim(\"$htseqpath/count_matrix.txt\", header=TRUE, sep=\"\\t\")\n"; 
print OUT "lib <- c($controllist,$explist) \n"; 
print OUT "replicates <- c($notNullModel1,$notNullModel2) \n"; 
print OUT "groups <- list(NDE = c($nullModel), DE = c($notNullModel1,$notNullModel2))\n"; 
print OUT "cname <- all[,1] \n"; 
print OUT "all <- all[,-1] \n"; 
print OUT "all = as.matrix(all) \n"; 
print OUT "CD <- new(\"countData\", data = all, replicates = replicates, libsizes = as.integer(lib), groups = groups) \n"; 
print OUT "library(parallel) \n"; 
print OUT "CD\@annotation <- as.data.frame(cname) \n"; 
print OUT "cl <- NULL \n"; 
print OUT "CDP.NBML <- getPriors.NB(CD, samplesize = 1000, estimation = \"QL\", cl = cl) \n"; 
print OUT "CDPost.NBML <- getLikelihoods.NB(CDP.NBML, pET = 'BIC', cl = cl) \n"; 
print OUT "CDPost.NBML\@estProps \n";  
print OUT "topCounts(CDPost.NBML, group=2) \n"; 
print OUT "NBML.TPs <- getTPs(CDPost.NBML, group=2, TPs = 1:100) \n"; 
print OUT "topCounts(CDPost.NBML, group=2)\n"; 
print OUT "blah <- topCounts(CDPost.NBML,group=\"DE\",FDR=1) \n"; 
print OUT "write.csv(blah, file=\"$analysispath/bayseq_de_analyzed_dataset_$analysisName.csv\") \n"; 

###################DESEQ2 Commands############################ 
print OUT "directory <- \"$htseqpath\" \n"; 
print OUT "sampleFiles <- c($greparray) \n";  #CREATE THE DESEQ2 object 
print OUT "sampleCondition <- c(rep(\"C\",$controlcount),rep(\"T\",$expcount)) \n"; 
print OUT "sampleTable<- data.frame(sampleName=sampleFiles, fileName=sampleFiles, condition=sampleCondition) \n"; 
print OUT "ddsHTSeq<-DESeqDataSetFromHTSeqCount(sampleTable=sampleTable, directory=directory, design=~condition) \n"; 
print OUT "dds<-DESeq(ddsHTSeq) \n"; 
print OUT "res<-results(dds) \n"; 
print OUT "res<-res[order(res\$padj),] \n"; 
print OUT "head(res) \n"; 
print OUT "write.table (as.data.frame(res), file=\"$analysispath/results_deseq2.txt\") \n"; 
############################################################## 

#######################EdgeR Commands################################ 
print OUT "library(edgeR) \n"; 
print OUT "group <- c($notNullModel1,$notNullModel2) \n"; 
print OUT "y <- DGEList(counts=all, group= group) \n"; 
print OUT "dge <- DGEList(counts=y, group=group, genes = cname ) \n";  #make the R object from the list of counts and the annotation                                
#vector from previous analyses 
print OUT "dge <- calcNormFactors(dge) \n"; 
print OUT "dge = estimateCommonDisp(dge) \n"; 
print OUT "de.com = exactTest(dge)   \n"; 
print OUT "topTags(de.com)  \n"; 
print OUT "goodList = topTags(de.com, n=\"good\") \n"; 
print OUT "write.table (as.data.frame(goodList), file=\"$analysispath/edgeR.txt\") \n"; 
##################################################################### 
close(OUT); 
system("R --vanilla <$rcommandpath"); 


$commands = $commands.$cmcommand.$cdcommand.$htseqcommand;


$echo $commands;
# echo commands for testing
#echo "$cmcommand<br>";
#echo "$cdcommand"; 

?>


</body>
</html>
