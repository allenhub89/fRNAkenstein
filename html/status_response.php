<style type="text/css">
#centercontainer {
	/* Internet Explorer 10 */
	display:-ms-flexbox;
	-ms-flex-pack:center;
	-ms-flex-align:center;

	/* Firefox */
	display:-moz-box;
	-moz-box-pack:center;
	-moz-box-align:center;

	/* Safari, Opera, and Chrome */
	display:-webkit-box;
	-webkit-box-pack:center;
	-webkit-box-align:center;

	/* W3C */
	display:box;
	box-pack:center;
	box-align:center;
}
</style>
<body>
<div id="centercontainer">
<center>
<h2 style="opacity:0.8;">Process listing:</h2><h4><br>
<?php

if(!empty($_SESSION['user_name']))
{
  header('Location: index.php');
}


$subdirectories = "/var/www/subdirectories_for_interface";

$scripts = scandir("$subdirectories/bash_scripts");


$fRNAkrunning = exec("ps aux | grep '[r]un_' ", $outputs);
if ( empty($fRNAkrunning) && count($outputs) == 0 && empty($scripts))
{
	echo "No processes currently queued or running!<br>";
} 
else {
	$files = array();
	if (!empty($fRNAkrunning) && count($outputs) > 0)
	{
		foreach($outputs as $proc)
		{ 
			preg_match("/(run_(.*)\.(.*)\.sh)/",$proc,$match);
			$file = $match[1];
			if($match[3] != '*')
			{
				array_push($files, $file);
				$id = $match[2];
				echo "Process with run ID: ".$id." is <font color='green'><b>running</b></font>!<br><br>";
			}
		}
	}
	if( !empty($scripts) )
	{
		
		foreach ($scripts as $script) 
		{
			if(($script != ".") and ($script != ".."))
			{ 
				if(!empty($files))
				{
					foreach($files as &$file)
					{
						if($file != $script)
						{
							preg_match("/run_(.*)\.(.*)\.sh/",$script,$match);
							$id = $match[1];
							echo "Process with run ID: ".$id." is <font color='yellow'><b>queued</b></font>!<br><br>";
							unset($files[$file]);

						}
					}
				}
				else
				{
					preg_match("/run_(.*).sh/",$script,$match);
					$id = $match[1];
					echo "Process with run ID: ".$id." is <font color='yellow'><b>queued</b></font>!<br><br>";
				}
			}
		} 	
	}
	
}

?>
<h3 style="opacity:0.3;">Refresh to check status again.</h2><h4><br>
</center>
</div>
</body>
