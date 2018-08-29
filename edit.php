<?php
require_once "pdo.php";
require_once "util.php";
session_start();

if ( !isset($_SESSION['user_id']) ) {
  die("Access denied");
  return;
}

if ( isset($_POST['cancel']) ){
  header( 'Location: index.php');
  return;
};

if ( !isset($_REQUEST['profile_id']) ) {
  $_SESSION['error'] = "Missing profile_id";
  header("Location: index.php");
  return;
}

  $sql = "SELECT * FROM profile WHERE profile_id = :prof AND user_id=:uid";
  $stmt = $pdo->prepare($sql);
  $stmt->execute(array(
    ':prof' => $_REQUEST['profile_id'],
    ':uid' => $_SESSION['user_id']
  ));
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  if ( $row === false ) {
      $_SESSION['error'] = 'Could not load profile';
      header( 'Location: index.php' ) ;
      return;
  };

  $fn = htmlentities($row['first_name']);
  $ln = htmlentities($row['last_name']);
  $e = htmlentities($row['email']);
  $h = htmlentities($row['headline']);
  $s = htmlentities($row['summary']);
  $p = $row['profile_id'];

if ( isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email']) && isset($_POST['headline']) && isset($_POST['summary']) ) {

    $msg = validateProfile();
    if ( is_string($msg) ) {
      $_SESSION['error'] = $msg;
      header("Location: edit.php?profile_id=".$_REQUEST["profile_id"]);
      return;
    };

    $msg = validatePos();
    if ( is_string($msg) ) {
      $_SESSON['error'] = $msg;
      header("Location: edit.php?profile_id=".$_REQUEST["profile_id"]);
      return;
    };

    // changed for assignment 1 specs
    $sql = 'UPDATE profile SET first_name = :fn, last_name = :ln, email = :em, headline = :he, summary = :su WHERE profile_id = :prof AND user_id = :uid';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(
      ':prof' => $_REQUEST['profile_id'],
      ':uid' => $_SESSION['user_id'],
      ':fn' => $_POST['first_name'],
      ':ln' => $_POST['last_name'],
      ':em' => $_POST['email'],
      ':he' => $_POST['headline'],
      ':su' => $_POST['summary']
    ));        
  
// Clear out the old position entries
    $stmtDel = $pdo->prepare('DELETE FROM position WHERE profile_id = :pid');
    $stmtDel->execute(array( ':pid' => $_REQUEST['profile_id'] ));

    // Insert the position entries

    $rank = 1;
    for($i=1; $i <= 9; $i++) {
        if ( ! isset($_POST['year'.$i]) ) continue;
        if ( ! isset($_POST['desc'.$i]) ) continue;
        $year = $_POST['year'.$i];
        $desc = $_POST['desc'.$i];
        $sqlPos = 'INSERT INTO position (profile_id, rank, year, description) VALUES ( :pid, :rank, :year, :desc)';
        $stmt = $pdo->prepare($sqlPos);
        $stmt->execute(array(
            ':pid' => $_REQUEST['profile_id'],
            ':rank' => $rank,
            ':year' => $year,
            ':desc' => $desc)
        );
        $rank++;
    };

    $_SESSION['success'] = 'Record Updated';
    header( 'Location: index.php' ) ;
    return;  
};

?>
<html>
<head>
  <title>Samuel Adams</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">
  <script src="https://code.jquery.com/jquery-3.2.1.js" integrity="sha256-DZAnKJ/6XZ9si04Hgrsxu/8s717jcIzLy3oi35EouyE=" crossorigin="anonymous"></script>
</head>
<body>
<h1>Edit Profile for <?php htmlentities($_SESSION['name']); ?></h1>
<?php 
  flashMessages(); 
  ?>
<form method="post" action="edit.php">
<input type="hidden" name="profile_id"
value="<?= htmlentities($_GET['profile_id']); ?>"
/>
<p>First Name:
<input type="text" name="first_name" value='<?= $fn ?>'></p>
<p>Last Name:
<input type="text" name="last_name" value='<?= $ln ?>'></p>
<p>Email:
<input type="text" name="email" value='<?= $e ?>'></p>
<p>Headline:
    <input type="text" name="headline" value='<?= $h ?>'></p>
<p>Summary:
    <textarea name="summary"><?= $s ?></textarea></p>
<p>Position: <input type="submit" id="addPos" value="+">
    <div id="position_fields">
    <?php
    // run SQL and loop
    $sqlPos = "SELECT * FROM position WHERE profile_id = :profile_id AND rank = :rank";
    $stmt = $pdo->prepare($sqlPos);
    // run loop over select
    for($count=1; $count <= 9; $count++) {
      $stmt->execute(array(
        ":profile_id" => $_REQUEST['profile_id'],
        ":rank" => ($count - 1)
      ));
      $row[$count] = $stmt->fetch(PDO::FETCH_ASSOC);
      $year[$count] = htmlentities($row[$count]['year']);
      $desc[$count] = htmlentities($row[$count]['description']);
      if ( !empty($year[$count]) && !empty($desc[$count]) ){
        echo('<div id="position'.$count.'"><p>Year: <input type="text" name="year'.$count.'" value="'.$year[$count].'" /><input type="button" value="-" onclick="$(\'#position'.$count.'\').remove();countPos--;return false;"></p><textarea name="desc'.$count.'" rows="8" cols="80">'.$desc[$count].'</textarea></div>');  
      };
    };
    ?>
    </div>
  </p>
<p><input type="submit" value="Save" onclick="validatePos();" />
<input type="submit" name="cancel" value="Cancel"></p>
</form>
<script>
// jQuery to add forms
  countPos = 0;
  $(document).ready(function(){
      window.console && console.log('Document ready called');
      $('#addPos').click(function(event){
          event.preventDefault();
          if ( countPos >= 9 ) {
              alert("Maximum of nine position entries exceeded");
              return;
          }
          countPos++;
          window.console && console.log("Adding position "+countPos);
          $('#position_fields').append(
              '<div id="position'+countPos+'"> \
              <p>Year: <input type="text" name="year'+countPos+'" value="" /> \
              <input type="button" value="-" \
              onclick="$(\'#position'+countPos+'\').remove();countPos--;return false;"></p> \
              <textarea name="desc'+countPos+'" rows="8" cols="80"></textarea>\
              </div>');
      });
  });
</script>
</body>
</html>