<?php
// FINISHED - I think...
session_start();
require_once("pdo.php");
require_once("util.php");

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

// set page variables
$fn = htmlentities($row['first_name']);
$ln = htmlentities($row['last_name']);
$e = htmlentities($row['email']);
$h = htmlentities($row['headline']);
$s = htmlentities($row['summary']);

// process second SQL to grab positions and EDU from DB in ul
$positions = loadPos($pdo, $_REQUEST['profile_id']);
$schools = loadEdu($pdo, $_REQUEST['profile_id']);

$countEdu = 0;
$countPos = 0;
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
			<?php
				echo('<p>Education:</p>'."\n");
				echo('<ul>'."\n");
				if ( count($schools) > 0 ) {
					foreach( $schools as $school ) {
						$countEdu++;
						echo('<li>'.htmlentities($school['year']).' : '.htmlentities($school['name']).'</li>');
					};
				};
				echo("</ul>");
			?>

			<?php
				echo('<p>Positions:</p>'."\n");
				echo('<ul>'."\n");
				if ( count($positions) > 0 ) {
					foreach( $positions as $position ) {
						$countPos++;
						echo('<li>'.htmlentities($position['year']).' : '.htmlentities($position['description']).'</li>');
					};
				};
				echo("</ul>");
			?>
			<p><a href="index.php">Done</a></p>
		</div>
	</body>
</html>