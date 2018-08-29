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

// set variables for profile DB data
$fn = htmlentities($row['first_name']);
$ln = htmlentities($row['last_name']);
$e = htmlentities($row['email']);
$h = htmlentities($row['headline']);
$s = htmlentities($row['summary']);
$p = $row['profile_id'];

// check post
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

    $msg = validateEdu();
    if ( is_string($msg) ) {
      $_SESSION['error'] = $msg;
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

    // Insert new position entries
    insertPositions($pdo, $_REQUEST['profile_id']);

// Clear out old education entries
    $stmt = $pdo->prepare('DELETE FROM education WHERE profile_id = :pid');
    $stmt->execute(array(':pid' => $_REQUEST['profile_id']));

    // insert new education entries
    insertEducations($pdo, $_REQUEST['profile_id']);

// success message
    $_SESSION['success'] = 'Record Updated';
    header( 'Location: index.php' ) ;
    return;  
};

// bring in positions and educations
$positions = loadPos($pdo, $_REQUEST['profile_id']);
$schools = loadEdu($pdo, $_REQUEST['profile_id']);

?>
<html>
<head>
  <title>Samuel Adams</title>
  <link rel="stylesheet" 
    href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" 
    integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" 
    crossorigin="anonymous">
  <link rel="stylesheet" 
    href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" 
    integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" 
    crossorigin="anonymous">
  <link rel="stylesheet" 
    href="https://code.jquery.com/ui/1.12.1/themes/ui-lightness/jquery-ui.css">
  <script
    src="https://code.jquery.com/jquery-3.2.1.js"
    integrity="sha256-DZAnKJ/6XZ9si04Hgrsxu/8s717jcIzLy3oi35EouyE="
    crossorigin="anonymous"></script>
  <script
    src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"
    integrity="sha256-T0Vest3yCU7pafRw9r+settMBX6JkKN06dqBnpQ8d30="
    crossorigin="anonymous"></script>
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

<?php
// populate education entries
$countEdu = 0;

echo('<p>Education: <input type="submit" id="addEdu" value="+">'."\n");
echo('<div id="edu_fields">'."\n");
if ( count($schools) > 0 ) {
  foreach( $schools as $school ) {
    $countEdu++;
    echo('<div id="edu'.$countEdu.'">');
    echo(
      '<p>Year:<input type="text" name="edu_year'.$countEdu.'" value="'.$school['year'].'" /> <input type="button" value="-" onclick="$(\'#edu'.$countEdu.'\').remove();return false;"></p><p>School: <input type="text" size="80" name="edu_school'.$countEdu.'" class="school" value="'.htmlentities($school['name']).'" />');
    echo("</div>");
  };
};
echo("</div></p>");

// populate position entries
$countPos = 0;
echo('<p>Position: <input type="submit" id="addPos" value="+">');
echo('<div id="position_fields">');
if ( count($positions) > 0 ) {
  foreach ( $positions as $position ) {
    $countPos++;
    echo('<div id="position'.$countPos.'">');
    echo(
      '<p>Year:<input type="text" name="year'.$countPos.'" value="'.htmlentities($position['year']).'" /> <input type="button" value="-" onclick="$(\'#position'.$countPos.'\').remove();return false;"></p><br>');
    echo('<textarea name="desc'.$countPos.'" rows="8" cols="80">');
    echo( htmlentities($position['description']) );
    echo('</textarea></div>');
  };
};
echo("</div></p>");

?>

<p><input type="submit" value="Save" onclick="validatePos();" />
<input type="submit" name="cancel" value="Cancel"></p>
</form>
<script>
function htmlentities(string){
  return $('<div/>').text(string).html();
};

countPos = <?= $countPos ?>;
countEdu = <?= $countEdu ?>;
// jQuery to add forms
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

      $('#addEdu').click(function(event){
          event.preventDefault();
          if ( countEdu >= 9 ) {
              alert("Maximum of nine education entries exceeded");
              return;
          };
          countEdu++;
          window.console && console.log("Adding education "+countEdu);

          var source = $("#edu-template").html();
          $('#edu_fields').append(source.replace(/@COUNT@/g,countEdu));

          $('.school').autocomplete({ source: "school.php" });

      });
  });
</script>
<script id="edu-template" type="text">
  <div id="edu@COUNT@">
    <p>Year: <input type="text" name="edu_year@COUNT@" value="" />
      <input type="button" value="-" onclick="$('#edu@COUNT@').remove();return false;"></p><br>
    <p>School: <input type="text" size="80" name="edu_school@COUNT@" class="school" value="" />
    </p>
  </div>
</script>
</body>
</html>