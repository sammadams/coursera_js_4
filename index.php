<?php
// FINISHED
require_once "pdo.php";
session_start();
?>
<html>
<head>
    <title>Samuel Adams</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.2.1.js" integrity="sha256-DZAnKJ/6XZ9si04Hgrsxu/8s717jcIzLy3oi35EouyE=" crossorigin="anonymous"></script>
</head>
<body>
<h1>Sam's Resume Registry</h1>
<?php
// display error flash
if ( isset($_SESSION['error']) ) {
    echo '<p style="color:red">'.$_SESSION['error']."</p>\n";
    unset($_SESSION['error']);
}
// display success flash
if ( isset($_SESSION['success']) ) {
    echo '<p style="color:green">'.$_SESSION['success']."</p>\n";
    unset($_SESSION['success']);
}
// show login link if not logged in
if ( !isset($_SESSION['name']) ) {
    echo('<a href="login.php">Please log in</a>');
};
// display table
echo('<table border="1">'."\n");
echo("<thead><td>Full Name</td><td>Headline</td>");
if ( isset($_SESSION['name']) ) {
    echo("<td>Actions</td>");};
echo("</thead>");
$sql = "SELECT profile_id, user_id, first_name, last_name, headline FROM profile";
$stmt = $pdo->query($sql);
while ( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) {
    echo("<tr><td>");
    echo('<a href="view.php?profile_id='.$row['profile_id'].'">');
    echo(htmlentities($row['first_name'])." ".htmlentities($row['last_name']));
    echo("</a></td><td>");
    echo(htmlentities($row['headline']));
    echo("</td>");
    // set condition for user_id to show actions
    if( isset($_SESSION['name']) ) {
        if( ( $_SESSION['user_id'] == $row['user_id'] ) ) {
            echo("<td>");
            echo('<a href="edit.php?profile_id='.$row['profile_id'].'">Edit</a> / ');
            echo('<a href="delete.php?profile_id='.$row['profile_id'].'">Delete</a>');
            echo('</td>');
        } else {
            echo("<td>No Access for this user</td>");
        };
    };
    echo("</tr>");
};
echo('</table>');
?>

<?php
if ( isset($_SESSION['name']) ) {
    echo('<a href="add.php">Add New Entry</a><br/>');
    echo('<a href=logout.php>Logout</a>');
};
?>
</body>
</html>
