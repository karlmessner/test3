<?PHP include('../includes/db2.php');?>

<?PHP
print_r($_GET);
// get email
$Email = mysql_real_escape_string($_POST['Email']);
$sql = "INSERT INTO mc_NotifyOnLaunch SET nol_email = '$Email'"; 
$result = mysql_query($sql);

echo ($result) ? "Success" : "Failed";
