<?php
// Visit this page in your browser: http://localhost/pglife/setup_check.php
// It tells you exactly what's wrong if login/signup isn't working.

echo "<h2>PG Life - Setup Check</h2><pre style='font-size:15px;line-height:1.6;'>";

echo "PHP version: " . phpversion() . "\n";
echo "mysqli extension loaded: " . (extension_loaded('mysqli') ? "YES ✅" : "NO ❌ (enable it in php.ini)") . "\n\n";

$host = "localhost";
$dbuser = "root";
$dbpass = "";
$dbname = "pglife";

echo "Trying to connect to MySQL (host=$host, user=$dbuser, db=$dbname)...\n";

mysqli_report(MYSQLI_REPORT_OFF); // don't throw here, we want to print our own message
$conn = @new mysqli($host, $dbuser, $dbpass, $dbname);

if ($conn->connect_error) {
    echo "❌ CONNECTION FAILED: " . $conn->connect_error . "\n\n";
    echo "Common fixes:\n";
    echo "1. Open XAMPP Control Panel and make sure MySQL is started (green).\n";
    echo "2. If your MySQL root user has a password, edit includes/db.php and set \$dbpass.\n";
    echo "3. Make sure a database named 'pglife' exists (import pglife.sql in phpMyAdmin).\n";
    echo "</pre>";
    exit;
}
echo "✅ Connected to MySQL successfully.\n\n";

$requiredTables = ['users', 'properties', 'amenities', 'property_amenities', 'interested_users', 'bookings'];
echo "Checking tables in database '$dbname':\n";
foreach ($requiredTables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        $count = $conn->query("SELECT COUNT(*) as c FROM $table")->fetch_assoc()['c'];
        echo "  ✅ $table (rows: $count)\n";
    } else {
        echo "  ❌ $table — MISSING! Import pglife.sql again in phpMyAdmin.\n";
    }
}

echo "\nIf every table above shows ✅, your database is fine.\n";
echo "Then open the browser console (press F12 → Console tab) while clicking Login/Signup —\n";
echo "any red error there will tell us exactly what's failing.\n";
echo "</pre>";

$conn->close();
?>
