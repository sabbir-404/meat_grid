<?php
require_once "../Project-root/db_connect.php";

$table = $_GET['table'] ?? '';
$id = intval($_GET['id'] ?? 0);
$allowed = ['policy_supply','policy_prices','policy_consumption','policy_alt_protein'];
if(!in_array($table,$allowed)) die("Invalid table");

// Fetch row
$row = $conn->query("SELECT * FROM $table WHERE id=$id")->fetch_assoc();

if($_SERVER['REQUEST_METHOD']==='POST'){
    $cols = [];
    foreach($_POST as $k=>$v){
        if($k!='id'){
            $val = $conn->real_escape_string($v);
            $cols[] = "$k='$val'";
        }
    }
    $sql = "UPDATE $table SET ".implode(',',$cols)." WHERE id=$id";
    $conn->query($sql);
    header("Location: admin-policy.php");
    exit;
}
?>
<!DOCTYPE html>
<html><head><title>Update Record</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/></head>
<body class="p-4">
<h3>Update <?= htmlspecialchars($table) ?> Record</h3>
<form method="post">
<?php foreach($row as $k=>$v): if($k=='id') continue; ?>
<div class="mb-3">
<label class="form-label"><?= $k ?></label>
<input type="text" class="form-control" name="<?= $k ?>" value="<?= htmlspecialchars($v) ?>"/>
</div>
<?php endforeach; ?>
<button type="submit" class="btn btn-primary">Save</button>
<a href="admin-policy.php" class="btn btn-secondary">Cancel</a>
</form>
</body></html>
