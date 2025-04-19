<?php
include 'connect.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryName = trim($_POST['category_name']); // Sanitize input

    if (empty($categoryName)) {
        echo json_encode(['status' => 'error', 'message' => 'Category name cannot be empty.']);
    } else {
        $sql = "INSERT INTO category (name) VALUES ('$categoryName')";
        if ($conn->query($sql)) {
            echo json_encode(['status' => 'success', 'message' => 'Category added successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . $conn->error]);
        }
    }
} 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    
    <title>Add Category</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

   

</head>
<body>
<!-- Add Category Button -->
<div class="button-container">
    <button id="addCategoryButton" class="btn btn-primary">Add Category</button>
</div>



<!-- Add Category Modal -->
<div id="addCategoryModal" class="modal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h2>Add Category</h2>
        <form id="addCategoryForm" method="POST">
            <input type="text" id="categoryName" name="category_name" placeholder="Enter category name" required>
            <button type="submit" class="btn btn-success">Add Category</button>
        </form>
        <div id="addCategoryStatus"></div>
    </div>
</div>
<?php
 $sql = "SELECT * FROM category";
 $result = $conn->query($sql); 

 // Check if the query was successful
 if ($result && $result->num_rows > 0) {
     echo "<table class='category-table'>";
     echo "<thead>";
     echo "<tr>";
     echo "<th>ID</th>";
     echo "<th>Category Name</th>";
     echo "<th>Edit</th>"; // Edit column
     echo "<th>Delete</th>"; // Delete column
     echo "</tr>";
     echo "</thead>";
     echo "<tbody>";

     // Loop through the categories and display them in the table
     while ($category = $result->fetch_assoc()) {
         echo "<tr data-category-id='{$category['id']}' data-category-name='" . htmlspecialchars($category['name']) . "'>";
         echo "<td>{$category['id']}</td>";
         echo "<td>{$category['name']}</td>";
         echo "<td class='action-btns'>
                 <button class='edit-btn' title='Edit'>
                     <i class='fas fa-edit'></i>
                 </button>
               </td>";
         echo "<td class='action-btns'>
        <button class='delete-btn' title='Delete'>
         <i class='fas fa-trash'></i>
     </button>
   </td>";
         echo "</tr>";
     }

     echo "</tbody>";
     echo "</table>";
 } else {
     echo "<p>No categories found.</p>";
 }

?>





</body>
</html>