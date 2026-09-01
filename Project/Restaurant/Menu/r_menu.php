
<?php
session_start();

include "../../Common/lib/dbConfig.php";

// Only restaurant users can access this page
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "restaurant") {
    header("Location: ../../Common/login/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// --------------------------------------------------
// OPEN / CLOSE SHOP
// --------------------------------------------------
if (isset($_POST["toggle_shop"])) {

    $status = $_POST["toggle_shop"];

    mysqli_query(
        $conn,
        "UPDATE restaurants SET is_open = $status WHERE user_id = $user_id"
    );

    header("Location: r_menu.php");
    exit();
}


// --------------------------------------------------
// DELETE ITEM
// --------------------------------------------------
if (isset($_GET["delete"])) {

    $item_id = $_GET["delete"];

    mysqli_query(
        $conn,
        "UPDATE menu_items
         SET is_deleted = 1
         WHERE item_id = $item_id
         AND user_id = $user_id"
    );

    header("Location: r_menu.php");
    exit();
}


// --------------------------------------------------
// ADD / EDIT ITEM
// --------------------------------------------------
if (isset($_POST["save_item"])) {

    $item_id = $_POST["item_id"] ?? 0;
    $name = mysqli_real_escape_string($conn, $_POST["item_name"]);
    $description = mysqli_real_escape_string($conn, $_POST["description"]);
    $price = $_POST["price"];
    $available = $_POST["is_available"];

    // Upload photo
    $photo = "";

    if (!empty($_FILES["photo"]["name"])) {

        if (!is_dir("uploads")) {
            mkdir("uploads");
        }

        $photo = time() . "_" . $_FILES["photo"]["name"];

        move_uploaded_file(
            $_FILES["photo"]["tmp_name"],
            "uploads/" . $photo
        );
    }


    // EDIT
    if ($item_id > 0) {

        if ($photo != "") {

            $sql = "UPDATE menu_items SET
                    item_name = '$name',
                    description = '$description',
                    price = $price,
                    photo = '$photo',
                    is_available = $available
                    WHERE item_id = $item_id
                    AND user_id = $user_id";

        } else {

            $sql = "UPDATE menu_items SET
                    item_name = '$name',
                    description = '$description',
                    price = $price,
                    is_available = $available
                    WHERE item_id = $item_id
                    AND user_id = $user_id";
        }

    }

    // ADD
    else {

        $photo_value = $photo == "" ? "NULL" : "'$photo'";

        $sql = "INSERT INTO menu_items
                (user_id, item_name, description, price, photo, is_available, is_deleted)
                VALUES
                ($user_id, '$name', '$description', $price,
                $photo_value, $available, 0)";
    }

    mysqli_query($conn, $sql);

    header("Location: r_menu.php");
    exit();
}


// --------------------------------------------------
// GET RESTAURANT INFORMATION
// --------------------------------------------------
$result = mysqli_query(
    $conn,
    "SELECT shop_name, is_open
     FROM restaurants
     WHERE user_id = $user_id"
);

$restaurant = mysqli_fetch_assoc($result);

$shop_name = $restaurant["shop_name"];
$is_open = $restaurant["is_open"];


// --------------------------------------------------
// GET MENU ITEMS
// --------------------------------------------------
$result = mysqli_query(
    $conn,
    "SELECT *
     FROM menu_items
     WHERE user_id = $user_id
     AND is_deleted = 0
     ORDER BY item_id"
);


// --------------------------------------------------
// GET ITEM FOR EDITING
// --------------------------------------------------
$edit_item = null;

if (isset($_GET["edit"])) {

    $edit_id = $_GET["edit"];

    $edit_result = mysqli_query(
        $conn,
        "SELECT *
         FROM menu_items
         WHERE item_id = $edit_id
         AND user_id = $user_id
         AND is_deleted = 0"
    );

    $edit_item = mysqli_fetch_assoc($edit_result);
}

?>

<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">

    <title>Manage Menu - FoodRush</title>

    <link rel="stylesheet" href="../../Restaurant/Menu/style1.css">

</head>

<body>


<!-- NAVIGATION -->
<div class="bar">

    <a href="../Dashboard/dashboard.php">Dashboard</a>

    <a href="../Menu/r_menu.php" class="on">Manage Menu</a>

    <a href="../Order/r_orders.php">Order Queue</a>

    <a href="../../Common/changePassword/changePassword.php">Profile</a>

    <a href="../../Common/logout.php">Logout</a>

    <span>
        <?= htmlspecialchars($shop_name) ?> · Restaurant
    </span>

</div>


<div class="wrap">

    <h2>Manage Menu</h2>


    <!-- SHOP STATUS -->
    <div class="box2">

        <b>Shop status:</b>

        <?php if ($is_open): ?>

            <span class="tag t-green">Open</span>

            <form method="POST" style="display:inline">

                <input type="hidden" name="toggle_shop" value="0">

                <button type="submit" class="inline dan">
                    Close shop
                </button>

            </form>

        <?php else: ?>

            <span class="tag t-red">Closed</span>

            <form method="POST" style="display:inline">

                <input type="hidden" name="toggle_shop" value="1">

                <button type="submit" class="inline">
                    Open shop
                </button>

            </form>

        <?php endif; ?>

    </div>


    <!-- MENU TABLE -->
    <table>

        <tr>

            <th>Photo</th>
            <th>Item</th>
            <th>Price</th>
            <th>Available</th>
            <th>Action</th>

        </tr>


        <?php while ($item = mysqli_fetch_assoc($result)): ?>

        <tr>

            <td>

                <?php if ($item["photo"] != ""): ?>

                    <img
                        src="uploads/<?= htmlspecialchars($item["photo"]) ?>"
                        width="50"
                        height="40"
                    >

                <?php else: ?>

                    IMG

                <?php endif; ?>

            </td>


            <td>
                <?= htmlspecialchars($item["item_name"]) ?>
            </td>


            <td>
                Tk <?= number_format($item["price"], 0) ?>
            </td>


            <td>

                <?php if ($item["is_available"]): ?>

                    <span class="tag t-green">
                        Yes
                    </span>

                <?php else: ?>

                    <span class="tag t-gray">
                        No
                    </span>

                <?php endif; ?>

            </td>


            <td>

                <a href="r_menu.php?edit=<?= $item["item_id"] ?>">
                    Edit
                </a>

                ·

                <a
                    href="r_menu.php?delete=<?= $item["item_id"] ?>"
                    onclick="return confirm('Delete this item?')"
                >
                    Delete
                </a>

            </td>

        </tr>

        <?php endwhile; ?>

    </table>


    <!-- ADD / EDIT FORM -->

    <?php if ($edit_item): ?>

        <h3>Edit Item</h3>

    <?php else: ?>

        <h3>Add New Item</h3>

    <?php endif; ?>


    <form method="POST" enctype="multipart/form-data">

        <input type="hidden"
               name="save_item"
               value="1">


        <?php if ($edit_item): ?>

            <input type="hidden"
                   name="item_id"
                   value="<?= $edit_item["item_id"] ?>">

        <?php endif; ?>


        <label>Item name</label>

        <input
            type="text"
            name="item_name"
            value="<?= htmlspecialchars($edit_item["item_name"] ?? "") ?>"
            required
        >


        <label>Description</label>

        <textarea name="description"><?= htmlspecialchars($edit_item["description"] ?? "") ?></textarea>


        <label>Price (Tk)</label>

        <input
            type="number"
            step="0.01"
            name="price"
            value="<?= htmlspecialchars($edit_item["price"] ?? "") ?>"
            required
        >


        <label>Photo</label>

        <input
            type="file"
            name="photo"
            accept="image/*"
        >


        <label>Availability</label>

        <select name="is_available">

            <option value="1"
                <?= isset($edit_item) && $edit_item["is_available"] == 1 ? "selected" : "" ?>>
                Available
            </option>

            <option value="0"
                <?= isset($edit_item) && $edit_item["is_available"] == 0 ? "selected" : "" ?>>
                Unavailable
            </option>

        </select>


        <?php if ($edit_item): ?>

            <button type="submit">
                Save Changes
            </button>

            <a href="r_menu.php">
                <button type="button" class="sec">
                    Cancel
                </button>
            </a>

        <?php else: ?>

            <button type="submit">
                Add Item
            </button>

        <?php endif; ?>


    </form>

</div>

</body>

</html>
```
