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

  $msg = validateEdu();
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

    insertPositions($pdo, $profile_id);
    insertEducations($pdo, $profile_id);

    $_SESSION['success'] = 'Record Added';
    header( 'Location: index.php' ) ;
    return;
};

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
  Education: <input type="submit" id="addEdu" value="+">
  <div id="edu_fields"></div>
</p>
<p>
  Position: <input type="submit" id="addPos" value="+">
  <div id="position_fields"></div>
</p>
<p><input type="submit" value="Add New"/>
<input type="submit" name="cancel" value="Cancel"></p>
</form>
<script>
function htmlentities(string){
  return $('<div/>').text(string).html();
};

  countPos = 0;
  countEdu = 0;

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