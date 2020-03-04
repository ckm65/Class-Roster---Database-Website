<?php
include("includes/init.php");
$header_nav_class5 = "current_page";

// open connection to database
$db = open_sqlite_db("secure/catalog.sqlite");

// Throw an exception for incorrect SQL, instead of being silent.
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
function print_record($record)

{
?>
  <tr>
    <td><?php echo htmlspecialchars($record["course_number"]); ?></td>
    <td><?php echo htmlspecialchars($record["course_name"]); ?></td>
    <td><?php echo htmlspecialchars($record["course_description"]); ?></td>
    <td><?php echo htmlspecialchars($record["term"]); ?></td>
    <td><?php echo htmlspecialchars($record["professor"]); ?></td>
    <td><?php echo htmlspecialchars($record["time"]); ?></td>
    <td><?php echo htmlspecialchars($record["credits"]); ?></td>
    <td><?php echo htmlspecialchars($record["requirement_fufilled"]); ?></td>

  </tr>
<?php
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title> Cornell University Information Science Course Catalog </title>
    <link rel="stylesheet" type="text/css" href="styles/theme.css" media="screen" />
</head>

<body>
<?php
include("includes/header.php");
$header_nav_class = "current_page";

?>



  <main>
    <h2><?php echo $title; ?></h2>
    <p>Welcome to Cornell University Information Science Course Catalog!</p>
    <?php
    $sql = "SELECT * FROM catalog WHERE requirement_fufilled = 'Core';";
    $result = exec_sql_query($db, $sql);
    $records = $result->fetchAll();
    ?>

    <!-- TODO: execute SQL query -->


    <table>
      <tr>
        <th>Course Number</th>
        <th>Course Name</th>
        <th>Course Description</th>
        <th>Term</th>
        <th>Professor</th>
        <th>Credits</th>
        <th>Requirement Fufilled</th>
      </tr>
  <?php
      foreach($records as $record) {
  print_record($record);
}
  ?>
      <!-- TODO: add rows to table. Hint: call print_record() -->
    </table>
    <?php
    ?>
  </main>

  <?php include("includes/footer.php"); ?>
</body>

</html>

  <!-- TODO: This should be your main page for your site. Remove this file when you're ready!-->
