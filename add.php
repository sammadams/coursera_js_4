<?php
require_once "pdo.php";
require_once "util.php";
session_start();

if ( !isset($_SESSION['user_id']) ) {
  die("Access Denied");
  return;
};

if ( isset($_POST['cancel']) ){
  header( 'Location: index.php');
  return;
};

if ( isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email']) && isset($_POST['headline']) && isset($_POST['summary']) ) {

  $msg = validatePos();
  if ( is_string($msg) ) {
    $_SESSION['error'] = $msg;
    header("Location: add.php");
    return;
  };

  $msg = validateProfile();
  if ( is_string($msg) ) {
    $_SESSION['error'] = $msg;
    header("Location: add.php");
    return;
  }

    // DONE: changed for JS_assignment_1 specs
    $sql = 'INSERT INTO profile (user_id, first_name, last_name, email, headline, summary) VALUES ( :uid, :fn, :ln, :em, :he, :su)';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(
      ':uid' => $_SESSION['user_id'],
      ':fn' => $_POST['first_name'],
      ':ln' => $_POST['last_name'],
      ':em' => $_POST['email'],
      ':he' => $_POST['headline'],
      ':su' => $_POST['summary'])
    ); 

    $profile_id = $pdo->lastInsertId();
    $sqlPos = 'INSERT INTO position (profile_id, rank, year, description) VALUES (:pid, :rank, :year, :desc)';

    $rank = 1;
    for($i=1; $i<=9; $i++) {
      if ( !isset($_POST['year'.$i]) ) continue;
      if ( !isset($_POST['desc'.$i]) ) continue;
      $year = $_POST['year'.$i];
      $desc = $_POST['desc'.$i];

      $stmtPos = $pdo->prepare($sqlPos);
      $stmtPos->execute(array(
        ':pid' => $profile_id,
        ':rank' => $rank,
        ':year' => $year,
        ':desc' => $desc)
      );
    $rank++;
    };

    $_SESSION['success'] = 'Record Added';
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
<p>Add A New Resume</p>
<?php
  flashMessages();
?>
<form method="post">
<p>First Name:
<input type="text" name="first_name"></p>
<p>Last Name:
<input type="text" name="last_name"></p>
<p>Email:
<input type="text" name="email"></p>
<p>Headline:
    <input type="text" name="headline"></p>
<p>Summary:
    <textarea name="summary" rows="8" cols="80"></textarea>
</p>
<p>
  Position: <input type="submit" id="addPos" value="+">
  <div id="position_fields"></div>
</p>
<p><input type="submit" value="Add New"/>
<input type="submit" name="cancel" value="Cancel"></p>
</form>
<script>
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