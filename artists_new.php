<?php
//import bloom filter library by mrspartak/php.bloom.filter

require 'bloom.class.php';

ini_set('display_errors', 'On');
ini_set('max_execution_time', 300);
error_reporting(E_ALL);

$mtime = microtime();
$mtime = explode(" ",$mtime);
$mtime = $mtime[1] + $mtime[0];
$starttime = $mtime; 

//open file for edit
$file = fopen("Artist_lists_small.txt", "r") or die("Unable to open file");


//create a bloom filter
$parameters = array('entries_max' => 1000);
$bloom = new Bloom($parameters);

//create an array with all the artists out of the file
$all_artists_array = array();

while(!feof($file)){

	$line = explode(",", fgets($file));
	
	foreach($line as $key => $value){
	
		if (!array_key_exists(trim($value), $all_artists_array)){
		
			$all_artists_array[trim($value)] = 1;
		
		}
		else{
			
			$all_artists_array[trim($value)] += 1;
		}
	
	
	}

}

//create a bloom filter by reducing artists to those who appear more than 50 times
foreach($all_artists_array as $key => $value){
	
	if ($all_artists_array[$key] >= 50){
	
		$bloom->set($key);
	}

}

//go through the $file another time, creating the pairs of artists with more than fifty occurences
rewind($file);
$artists_pairs_array = array();

while(!feof($file)){
	
	$line = explode(",", fgets($file));
	
	foreach($line as $key => $value){
		
		if ($bloom->has(trim($value))){
			
			foreach($line as $key_inner => $value_inner){
				
				if($bloom->has(trim($value_inner)) && ($value != $value_inner)){
					
					
					$artist_pair = $value.$value_inner;
					$artist_pair_reversed = $value_inner.$value;
					
					if (array_key_exists(trim($artist_pair), $artists_pairs_array))
						$artists_pairs_array[trim($artist_pair)] += 1;
					elseif (array_key_exists(trim($artist_pair_reversed), $artists_pairs_array))
						$artists_pairs_array[trim($artist_pair_reversed)] += 1;
					else
						$artists_pairs_array[trim($artist_pair)] = 1;
						
				
				}
			
			
			}
		
		}
	
	}
	

}

fclose($file);
//var_dump($artists_pairs_array);
//open file for writing pairs
$file_writable = fopen("artists_pairs.txt", "w") or die ("Unable to open file!");
$counter = 1;

foreach($artists_pairs_array as $key => $value){
	
	if ($artists_pairs_array[$key] >= 50){
		
		fwrite($file_writable, $counter. " ". $key . "\n"); 
		$counter++;
	
	}

}

fclose($file_writable);

$mtime = microtime();
$mtime = explode(" ",$mtime);
$mtime = $mtime[1] + $mtime[0];
$endtime = $mtime;
$totaltime = ($endtime - $starttime);
echo "Execution Time: ".$totaltime." seconds"; 


?>
	