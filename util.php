<?php

function flashMessages() {
	if ( isset($_SESSION['error']) ) {
		echo('<p style="color:red;">'.htmlentities($_SESSION['error'])."</p>");
		unset($_SESSION['error']);
	};
	if ( isset($_SESSION['success']) ) {
		echo('<p style="color: green;">'.htmlentities($_SESSION['success'])."</p>");
		unset($_SESSION['success']);
	};
};

function validateProfile() {
	if ( strlen($_POST['first_name']) == 0 || strlen($_POST['last_name']) == 0 || strlen($_POST['email']) == 0 || strlen($_POST['headline']) == 0 || strlen($_POST['summary']) == 0 ) {
		return "All fields are required";
	} 

	if ( strpos($_POST['email'],'@') === false ) {
		return "Email address must contain @";
	} 
	return true;
}

function validatePos() {
	for($i=1; $i<=9; $i++) {
		if ( ! isset($_POST['year'.$i]) ) continue;
		if ( ! isset($_POST['desc'.$i]) ) continue;
		$year = $_POST['year'.$i];
		$desc = $_POST['desc'.$i];
		if ( strlen($year) == 0 || strlen($desc) == 0 ) {
		  return "All position fields are required";
		}
		if ( ! is_numeric($year) ) {
		  return "Position year must be numeric";
		}
  };
  return true;
};

function validateEdu() {
	for($i=1; $i<=9; $i++) {
		if ( ! isset($_POST['edu_school'.$i]) ) continue;
		if ( ! isset($_POST['edu_year'.$i]) ) continue;
		$school = $_POST['edu_school'.$i];
		$year = $_POST['edu_year'.$i];
		if ( strlen($year) == 0 || strlen($school) == 0 ) {
		  return "All education fields are required";
		}
		if ( ! is_numeric($year) ) {
		  return "Graduation year must be numeric";
		}
  };
  return true;
};
 
// loads all positions for a profile
function loadPos($pdo, $profile_id) {
	$stmt = $pdo->prepare('SELECT * FROM Position WHERE profile_id = :prof ORDER BY rank');
	$stmt->execute(array( ':prof' => $profile_id));
	$positions = $stmt->fetchAll(PDO::FETCH_ASSOC);
	return $positions;
};

// inserts new positions
function insertPositions($pdo, $profile_id) {
	$rank = 1;
	for($i=1; $i<=9; $i++) {
		if ( !isset($_POST['year'.$i]) ) continue;
		if ( !isset($_POST['desc'.$i]) ) continue;
		$year = $_POST['year'.$i];
		$desc = $_POST['desc'.$i];

		$sql = 'INSERT INTO position (profile_id, rank, year, description) VALUES ( :pid, :rank, :year, :desc)';
		$stmt = $pdo->prepare($sql);
		$stmt->execute(array(
			':pid' => $profile_id, 
			':rank' => $rank, 
			':year' => $year, 
			':desc' => $desc
		));
		$rank++;
	};
};

// loads all education for profile, many-to-many
function loadEdu($pdo, $profile_id) {
	$stmt = $pdo->prepare('SELECT year, name FROM education JOIN institution ON education.institution_id = institution.institution_id WHERE profile_id = :prof ORDER BY rank');
	$stmt->execute(array( ':prof' => $profile_id ));
	$educations = $stmt->fetchAll(PDO::FETCH_ASSOC);
	return $educations;
}

// inserts new education
function insertEducations($pdo, $profile_id) {
	$rank = 1;
	for($i=1; $i<=9; $i++) {
		if ( !isset($_POST['edu_year'.$i]) ) continue;
		if ( !isset($_POST['edu_school'.$i]) ) continue;
		$year = $_POST['edu_year'.$i];
		$school = $_POST['edu_school'.$i];

		$institution_id = false;
		$sql = 'SELECT institution_id FROM institution WHERE name = :name';
		$stmt = $pdo->prepare($sql);
		$stmt->execute(array(':name' => $school));
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		if ( $row !== false ) $institution_id = $row['institution_id'];

		// no institution
		if ( $institution_id === false ) {
			$sql = 'INSERT INTO institution (name) VALUES (:name)';
			$stmt = $pdo->prepare($sql);
			$stmt->execute(array(':name'=>$school));
			$institution_id = $pdo->lastInsertId();
		}

		$sql = 'INSERT INTO education (profile_id, rank, year, institution_id) VALUES ( :pid, :rank, :year, :iid )';
		$stmt = $pdo->prepare($sql);
		$stmt->execute(array(
			':pid' => $profile_id,
			':rank' => $rank,
			':year' => $year,
			':iid' => $institution_id
		));

		$rank++;
	};
};