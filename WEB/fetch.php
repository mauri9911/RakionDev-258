<?php
// impediment url
$url = $_SERVER['QUERY_STRING'];

// Evitar errores si la URL no trae los parámetros esperados
$parts = explode("&", $url);
$app = isset($parts[0]) ? $parts[0] : '';
$ver = isset($parts[1]) ? $parts[1] : '';

if(!is_numeric($app) || !is_numeric($ver)) {
	echo "Error";
	exit();
}

if($app != round($app) || $ver != round($ver)) {
	echo "Error";
	exit();
}

// DB Config
$config['db_host'] = 'localhost';
$config['db_base'] = 'rakion';
$config['db_user'] = 'root';
$config['db_pass'] = '1234567';

// Conexión usando mysqli (Soporte para PHP 7+)
$conn = mysqli_connect($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_base']);

if ($conn)
{
	// Limpiar variables para evitar inyección SQL (aunque sean numéricas, por seguridad)
	$app = mysqli_real_escape_string($conn, $app);
	$ver = mysqli_real_escape_string($conn, $ver);

	// Fetch App
	$queryApp = mysqli_query($conn, "SELECT * FROM `fetchapp` WHERE AppId = '$app'");
	$dbapp = mysqli_fetch_array($queryApp);

	if ($dbapp)
	{
		if($dbapp['VerLimit'] == $ver)
		{
			exit();
		}

		if($dbapp['VerLimit'] > $ver)
		{
			echo "+".$dbapp['NoticeUrl']."\n";
			echo "=".$dbapp['FileUrl']."\n";
			
			// Fetch File Summary
			$queryFile = mysqli_query($conn, "SELECT count(*), sum(FileSize) FROM `fetchfile` WHERE FileVer > '$ver' AND AppId = '$app'");
			$dbfile = mysqli_fetch_array($queryFile);
			
			echo "~".$dbfile['count(*)'].";".$dbfile['sum(FileSize)'].";".$dbapp['VerLimit']."\n";
			
			// Fetch File List
			$dbfile2 = mysqli_query($conn, "SELECT * FROM `fetchfile` WHERE FileVer > '$ver' AND AppId = '$app'");
			while($data = mysqli_fetch_array($dbfile2)){
				echo "".$data['Command'].";".$data['FileDir'].";".$data['FileIns'].";".$data['FileVer'].";".$data['FileSize']."\n";
			}
		} 
		else 
		{
			echo "Error";
		}
	}
	mysqli_close($conn);
}
?>