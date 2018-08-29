<?php
// FINISHED - I think...
session_start();
require_once("pdo.php");

// process SQL statement
$sql = "SELECT * FROM profile WHERE profile_id = :profile_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(array(":profile_id" => $_GET['profile_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $row === false ) {
    $_SESSION['error'] = 'Bad value for profile_id';
    header( 'Location: index.php' ) ;
    return;
};

/*
$rank = 1;
    for($i=1; $i<=9; $i++) {
      if ( !isset($_POST['year'.$i]) ) continue;
      if ( !isset($_POST['descr'.$i]) ) continue;
      $year = $_POST['year'.$i];
      $desc = $_POST['desc'.$i];

      $sql2 = 'INSERT INTO Position (profile_id, rank, year, description) VALUES (:pid, :rank, :year, :desc)';
      $stmt = $pdo->prepare($sql2);
      $stmt->execute(array(
        ':pid' => $profile_id,
        ':rank' => $rank,
        ':year' => $year,
        ':desc' => $desc)
      );
    $rank++;
    };
 */

// set page variables
$fn = htmlentities($row['first_name']);
$ln = htmlentities($row['last_name']);
$e = htmlentities($row['email']);
$h = htmlentities($row['headline']);
$s = htmlentities($row['summary']);

// process second SQL to grab positions from DB in ul

?>

<html>
	<head>
		<title>Samuel Adams</title>
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
	    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">
	    <script src="https://code.jquery.com/jquery-3.2.1.js" integrity="sha256-DZAnKJ/6XZ9si04Hgrsxu/8s717jcIzLy3oi35EouyE=" crossorigin="anonymous"></script>
	</head>
	<body>
		<div class="container">
			<h1>Profile information</h1>
			<p>First Name: 
				<?php echo($fn) ?></p>
			<p>Last Name: 
				<?php echo($ln) ?></p>
			<p>Email: 
				<?php echo($e) ?> </p>
			<p>Headline:<br/>
				<?php echo($h) ?> </p>
			<p>Summary:<br/>
				<?php echo($s) ?> </p>
			<p>Position:</p>
			<ul>
				<?php
				$sqlPos = "SELECT * FROM Position WHERE profile_id = :profile_id AND rank = :rank";
				$stmt2 = $pdo->prepare($sqlPos);
				// run loop over select
				for($i=1;$i<=9;$i++) {
					$stmt2->execute(array(
						":profile_id" => $_GET['profile_id'],
						":rank" => $i
					));
					$row[$i] = $stmt2->fetch(PDO::FETCH_ASSOC);
					$year[$i] = htmlentities($row[$i]['year']);
					$desc[$i] = htmlentities($row[$i]['description']);
					if ( !empty($year[$i]) && !empty($desc[$i]) ){
						echo("<li>".$year[$i].": ".$desc[$i]."</li>");	
					};
				};

				?>
			</ul>
			<?php // need to add positions here ?>
			<p><a href="index.php">Done</a></p>
		</div>
	</body>
</html>