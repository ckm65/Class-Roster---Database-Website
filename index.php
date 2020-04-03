<?php

include("includes/init.php");
$header_nav_class = "current_page";

$db = open_sqlite_db("secure/catalog.sqlite");


$user_messages = array();

$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
function print_record($record1)

{
?>

  <tr>
    <td><?php echo htmlspecialchars($record1["course_number"]); ?></td>
    <td><?php echo htmlspecialchars($record1["course_name"]); ?></td>
    <td><?php echo htmlspecialchars($record1["course_description"]); ?></td>
    <td>
      <?php
      $star = intval($record1["course_rating"]);
      for ($s = 1; $s <= 5; $s++) {
        if ($s <= $star) {
          echo "★";
        } else {
          echo "☆";
        }
      }
      ?>
    <td><?php echo htmlspecialchars($record1["term"]); ?></td>
    <td><?php echo htmlspecialchars($record1["professor"]); ?></td>
    <td><?php echo htmlspecialchars($record1["time"]); ?></td>
    <td><?php echo htmlspecialchars($record1["credits"]); ?></td>
    <td><?php echo htmlspecialchars($record1["requirement_fufilled"]); ?></td>


  </tr>
<?php
}
// Search Form by Requirement

const SEARCH_FIELDS = [
  "all" => "Search All Courses",
  "course_rating" => "Search Course Ratings",
  "term" => "Search by Term",
  "time" => "Search by Time",
  "credits" => "Search by Credits",
  "requirement_fufilled" => "Search by Requirement"
];

if (isset($_GET['search'])) {
  $do_the_search = TRUE;

  $category = filter_input(INPUT_GET, 'category', FILTER_SANITIZE_STRING);
  if (in_array($category, array_keys(SEARCH_FIELDS))) {
    $search_field = $category;
  } else {
    array_push($user_messages, "Invalid category for search.");
    $do_search = FALSE;
  }

  $search = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING);
  $search = trim($search);
} else {

  $do_the_search = FALSE;
  $category = NULL;
  $search = NULL;
}

// Insert Adding a Course

$options = exec_sql_query($db, "SELECT DISTINCT requirement_fufilled FROM catalog", NULL)->fetchAll(PDO::FETCH_COLUMN);

$options2 = exec_sql_query($db, "SELECT DISTINCT term FROM catalog", NULL)->fetchAll(PDO::FETCH_COLUMN);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $valid_review = TRUE;

  $course_number = filter_input(INPUT_POST, 'course_number', FILTER_SANITIZE_STRING);
  $course_name = filter_input(INPUT_POST, 'course_name', FILTER_SANITIZE_STRING);
  $course_description= filter_input(INPUT_POST, 'course_description', FILTER_SANITIZE_STRING);
  $term = filter_input(INPUT_POST, 'term', FILTER_SANITIZE_STRING);
  $professor = filter_input(INPUT_POST, 'professor', FILTER_SANITIZE_STRING);
  $course_rating = filter_input(INPUT_POST, 'course_rating', FILTER_VALIDATE_INT);
  $time = filter_input(INPUT_POST, 'time', FILTER_SANITIZE_STRING);
  $credits = filter_input(INPUT_POST, 'credits', FILTER_VALIDATE_INT);
  $requirement_fufilled = filter_input(INPUT_POST, 'requirement_fufilled', FILTER_SANITIZE_STRING);

// course number is required

  if ($course_number < 1000 || $course_number > 7000) {
    $valid_review = FALSE;
   }

// couse name is required
if ($course_name =='') {
  $valid_review = FALSE;
}
// course description is required
if ($course_description =='') {
  $valid_review = FALSE;

}
// profesor is required
if ($professor =='') {
  $valid_review = FALSE;

}
// time is required
if ($time =='') {
  $valid_review = FALSE;

}
//  rating required
 if ($course_rating < 1 || $course_rating > 5) {
   $valid_review = FALSE;
  }

// term is  required
if (!in_array($term, $options2)) {
  $valid_review = FALSE;
}
 // credits
  if ($credits < 0|| $credits> 4) {
  $valid_review = FALSE;
}

// requirement fufilled is  required
if (!in_array($requirement_fufilled, $options)) {
  $valid_review = FALSE;
}
  // insert valid reviews into database
  if ($valid_review) {

    $sql = "INSERT INTO catalog(course_number, course_name, course_description, course_rating, term, professor, time, credits, requirement_fufilled) VALUES (:value1,:value2,:value3,:value4,:value5,:value6,:value7,:value8,:value9);";
    $params = array(
      ':value1' => $course_number,
      ':value2' => $course_name,
      ':value3' => $course_description,
      ':value4' => $course_rating,
      ':value5' => $term,
      ':value6' => $professor,
      ':value7' => $time,
      ':value8' => $credits,
      ':value9' => $requirement_fufilled,

    );
    $result = exec_sql_query($db, $sql, $params);

    if ($result) {
      array_push($user_messages, "Your Course has been added to the Catalog");
    } else {
      array_push($user_messages, "Failed to add Course.");
    }
  } else {
    array_push($user_messages, "Failed to add Course. Invalid inputs.");
  }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title> Cornell University Information Science Course Catalog </title>
    <link rel="stylesheet" type="text/css" href="styles/theme.css" media="screen" />
</head>

<h1> Cornell University Information Science Course Catalog </h1>
<body>



  <main>
    <h2><?php echo $title; ?></h2>
    <p><strong>Welcome to Cornell University Information Science Course Catalog!</strong></p>
    <?php
    $sql = "SELECT * FROM catalog;";
    $result = exec_sql_query($db, $sql);
    $records1 = $result->fetchAll();
    ?>
    <?php

    foreach ($user_messages as $user_message) {
      echo "<p><strong>" . htmlspecialchars($user_message) . "</strong></p>\n";
    }
    ?>

    <form id="searchCatalog" action="index.php" method="get" novalidate>
      <select name="category">
        <?php foreach (SEARCH_FIELDS as $field_name => $label) { ?>
          <option value="<?php echo $field_name; ?>"><?php echo $label; ?></option>
        <?php } ?>
      </select>
      <input type="text" name="search" required />
      <button type="submit">Search</button>
    </form>

    <?php
    if ($do_the_search) {  ?>

      <h2> Search Results </h2>
      <?php
      if ($search_field == "all") {
        // Search across all courses at once!
        $sql = "SELECT * FROM catalog WHERE (course_rating LIKE '%' ||:course_rating||'%') OR (term LIKE '%' ||:term||'%') OR (time LIKE '%' ||:time||'%') OR (credits LIKE '%' ||:credits||'%') OR (requirement_fufilled LIKE '%' ||:requirement_fufilled||'%');";
        $params = array(
          ':course_rating' => $search,
          ':term' => $search,
          ':time' => $search,
          ':credits' => $search,
          ':requirement_fufilled' => $search,

        );

      } else {
        // Search across the specified course field field
        $sql="SELECT * FROM catalog WHERE ($search_field LIKE '%' || :search || '%');";
        $params = array(
          ':search' => $search
        );
      }
    } else {
      ?>

<?php

$sql = "SELECT * FROM catalog";
$params = array();
}

$result = exec_sql_query($db, $sql, $params);
if ($result) {

$records1 = $result->fetchAll();

if (count($records1) > 0) {

?>


    <table>
      <tr>
        <th>Course Number</th>
        <th>Course Name</th>
        <th>Course Description</th>
        <th> Course Rating </th>
        <th>Term</th>
        <th>Professor</th>
        <th>Time</th>
        <th>Credits</th>
        <th>Requirement Fufilled</th>
      </tr>


  <?php
      foreach($records1 as $record1) {
  print_record($record1);
}


  ?>
    </table>
    <?php
      } else {

        echo "<p>No matching reviews found.</p>";
      }
    }
    ?>

    <?php
    ?>

  </main>

  <h2>Add a Information Science Course Catalog</h2>

    <form id="addCourse" action="index.php" method="post" novalidate>
    <div class="form">
        <label>Course Number:</label>
        <input type="text" name="course_number" />
      </div>
      <div class="form">
        <label>Course Name:</label>
        <input type="text" name="course_name" />
      </div>

      <div class="form">
        <label>Course Description:</label>
        <textarea name="course_description" cols="40" rows="5"></textarea>
      </div>

      <?php function print_rating($memo){
        $memo = intval ($memo);
            echo "<input id='rating".$memo."' type='radio' name='course_rating' value= '".$memo."' ";
            if ($memo == 5){ echo 'checked';}
            echo " /><label for='rating".$memo."'>".$memo."</label>";


}
?>
      <div class="form">
        <label>Course Rating:</label>
        <div>
          <?php
                for ($memo = 5; $memo >0; $memo--){
                  $memo = intval($memo);
                  print_rating ($memo);
                }


                    ?>

          <!-- <input id="rating5" type="radio" name="course_rating" value="5" checked /><label for="rating5">5</label>
          <input id="rating4" type="radio" name="course_rating" value="4" /><label for="rating4">4</label>
          <input id="rating3" type="radio" name="course_rating" value="3" /><label for="rating3">3</label>
          <input id="rating2" type="radio" name="course_rating" value="2" /><label for="rating2">2</label>
          <input id="rating1" type="radio" name="course_rating" value="1" /><label for="rating1">1</label> -->
        </div>
</div>
<div class="form">
        <label>Term:</label>
        <select name="term" required>
          <option value="" selected disabled>Term</option>
          <?php
          foreach ($options2 as $option) {
            echo "<option value=\"" . htmlspecialchars($option) . "\">" . htmlspecialchars($option) . "</option>";
          }
          ?>
        </select>

      </div>
      <div class="form">
        <label>Professor:</label>
        <input type="text" name="professor" />
      </div>

      <div class="form">
        <label>Time:</label>
        <input type="text" name="time" />
      </div>

      <div class="form">
          <label for="user">Credits:</label>
          <input type="number" id="user" name="credits" min="1"  />
        </div>


        <div class="form">
        <label>Requirement Fufilled:</label>
        <select name="requirement_fufilled" required>
          <option value="" selected disabled>Requirement Fufilled</option>
          <?php
          foreach ($options as $option) {
            echo "<option value=\"" . htmlspecialchars($option) . "\">" . htmlspecialchars($option) . "</option>";
          }
          ?>
        </select>

      </div>

      <div class="form">
        <span>
          </span>
        <button type="submit">Add Course</button>
      </div>
      </li>
      </ul>
    </form>



  <?php include("includes/footer.php"); ?>
</body>

</html>
