<html>
<head>
	<meta charset="UTF-8"> 
	<style>
		table, td, th{
			border:	1 solid black;
		}
		p{
			margin-left: 5px;
		}
	</style>
	<title>Request</title>
</head>
<body>
<?php

function startsWith($haystack, $needle)
{
    return $needle === "" || strpos($haystack, $needle) === 0;
}

function isTypeString($str_typename){
	if(startsWith($str_typename, "varchar")){
		return True;
	}
	if(startsWith($str_typename, "datetime")){
		return True;
	}
	return False;
}


$columns_num;

//General
$DEBUG = False; // show values in a table
$table = "users_test";
$path = "mit18_07_2014.txt";
$handle = null;
$sep = "	";
//Database informations
$server = "localhost";
$user = "root";
$password = "";
$base = "annuaire";
$encode = "utf8";
$columns = [];
$out = "INSERT INTO `".$table."` VALUES";

if(($db = mysqli_connect($server, $user, $password, $base)) == False){         
	exit("Connection error");
} 

$resultat = mysqli_query($db, "DESCRIBE users_test"); //encodage

while($fetch_result = mysqli_fetch_assoc($resultat)){
	$columns += array($fetch_result["Field"] => [isTypeString($fetch_result["Type"]), $fetch_result['Null'] == "YES"]);
}
mysqli_free_result($resultat);	


function printer($buffer, $sep, $tag){
	global $DEBUG, $columns, $columns_num;
	if($DEBUG){
		echo "<tr>";
	}
	$out = "";
	$tab_words = explode($sep, $buffer);
	if($tag == "th"){
		// no ascii SSMS SQL firsts chars
		$tab_words[0] = substr($tab_words[0], 3); 
		$columns_num = $tab_words;
	}
	$id_key = str_split($tab_words[0]);
	foreach ($tab_words as $key => $value) {
		if($DEBUG){
			echo "<".$tag.">".$value."</".$tag.">";
		}
		if($tag == "td"){
			$be_par = $columns[$columns_num[$key]][0] ? '\'' : "";
			$be_null = $columns[$columns_num[$key]][1] ? "NULL" : $value;
			
			if(strlen($value) == 0){
				$out = $out." ".$be_null.",";
			} else {
				$out = $out." ".$be_par.str_replace("'", " ", $value).$be_par.",";
			}
		} else {
			//$out = $out." `".$value."`,";
		}
	}
	$out = rtrim($out, ",");
	if($DEBUG){
		echo "</tr>"; 
	}
	return $out;
}

try{
	if(!file_exists($path)){
	    throw new Exception (sprintf('file « %s » doesn\'t exist.', $path));
	}
	$handle = fopen($path, "r"); 
	if($handle == false){
	    throw new Exception (sprintf('load error of « %s » .', $path));
	}
} catch(Exception $e) {
    echo $e->getMessage();
}

if(($db = mysqli_connect($server, $user, $password, $base)) == False){
	exit("Connection error");
} 

mysqli_query($db, "SET NAMES '".$encode."'"); //encodage

$resultat = mysqli_query($db, 'SELECT * FROM `'.$table.'`');
while($fetch_result = mysqli_fetch_assoc($resultat)){
	foreach ($fetch_result as $key => $value) {
	}
}
mysqli_free_result($resultat);	

if ($DEBUG) {
	echo "<table id=\"values\">";
}
$buffer = fgets($handle, 4096);
$buffer = str_replace("\r", "", $buffer);
$buffer = str_replace("\n", "", $buffer);
printer($buffer, $sep, "th");
//$out = rtrim($out, ",");
//$out = $out.") VALUES";

$nb_lines = 0;
$out2 = "";
while (($buffer = fgets($handle, 4096)) !== false) {
	$buffer = str_replace("\r", "", $buffer);
	$buffer = str_replace("\n", "", $buffer);
	$out2 = $out2." (";
	$out2 = $out2.printer($buffer, $sep, "td");
	$out2 = $out2." ),";
	
	if($nb_lines == 5){
		$out2 = rtrim($out2, ",");
		$out2 = $out2.";";
		if(!mysqli_query($db, $out.$out2)){
			 printf("<p>Erreur : %s\n<p>%s</p></p>", 
			 	mysqli_error($db), $out.$out2);
			 exit();
		}
		echo $out.$out2;
		$out2 = "";
		$nb_lines = 0;
	}

	$nb_lines++;	
};
if($DEBUG){
	echo "</table>";
}
if (!feof($handle)) {
	echo "Error: unexpected fgets() fail\n";
}
fclose($handle);
mysqli_close($db);


/*
recuperer la description de la table
ouvrir le fichier
pour chaque ligne du fichier
		
*/
?>
</body>
</html>