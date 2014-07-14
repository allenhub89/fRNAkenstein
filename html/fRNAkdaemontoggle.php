<?php

if($_SESSION['user_name'] == 'fRNAkadmin' || $_SESSION['user_name'] == 'toast')
{
	if ( empty($fRNAkrunning) )
		{
			echo "fRNAkDaemon is off";
			
		}
		else
		{
			echo "fRNAkdaemon is on";
			echo "fRNAkdaemon process: ".$fRNAkrunning;
		}
}

?>
