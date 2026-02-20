<?php
// --- 1. ส่วนตั้งค่าและการเชื่อมต่อ (บนสุดของไฟล์) ---
error_reporting(E_ALL); 
ini_set('display_errors', 1); // เปิดให้หน้าจอโชว์ Error ถ้าโค้ดพัง

$conn = mysqli_connect("localhost", "root", "", "health_tracker_db");
if (!$conn) {
    die("เชื่อมต่อฐานข้อมูลไม่ได้: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8");

// --- 2. ส่วนรับค่าจากฟอร์ม ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // เช็กว่ามาจากฟอร์มอาหาร
    if (isset($_POST['add_food'])) {
        $name = $_POST['food_name'];
        $cal = (int)$_POST['calories'];
        $sql = "INSERT INTO food_logs (food_name, calories, log_date) VALUES ('$name', $cal, CURDATE())";
        if (mysqli_query($conn, $sql)) {
            header("Location: index.php?status=success");
            exit();
        } else {
            echo "SQL Error: " . mysqli_error($conn);
        }
    }

    // เช็กว่ามาจากฟอร์มออกกำลังกาย
    if (isset($_POST['add_workout'])) {
        $act = $_POST['activity'];
        $burn = (int)$_POST['burn_calories'];
        $sql = "INSERT INTO workout_logs (activity, burn_calories, log_date) VALUES ('$act', $burn, CURDATE())";
        if (mysqli_query($conn, $sql)) {
            header("Location: index.php?status=success");
            exit();
        } else {
            echo "SQL Error: " . mysqli_error($conn);
        }
    }
}

// --- 3. ส่วนลบข้อมูล ---
if (isset($_GET['del_f'])) {
    $id = (int)$_GET['del_f'];
    mysqli_query($conn, "DELETE FROM food_logs WHERE id = $id");
    header("Location: index.php");
}
if (isset($_GET['del_w'])) {
    $id = (int)$_GET['del_w'];
    mysqli_query($conn, "DELETE FROM workout_logs WHERE id = $id");
    header("Location: index.php");
}

// --- 4. ส่วนคำนวณสรุปผล (เอาทุกรายการใน DB มาโชว์ก่อนเพื่อเช็กความชัวร์) ---
$res_in = mysqli_query($conn, "SELECT SUM(calories) as total FROM food_logs");
$total_in = mysqli_fetch_assoc($res_in)['total'] ?? 0;

$res_out = mysqli_query($conn, "SELECT SUM(burn_calories) as total FROM workout_logs");
$total_out = mysqli_fetch_assoc($res_out)['total'] ?? 0;
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Debug Health Tracker</title>
    <style>
        body { font-family: sans-serif; background: #f0f0f0; padding: 20px; }
        .box { background: white; padding: 15px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .flex { display: flex; gap: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        button { cursor: pointer; padding: 8px; width: 100%; margin-top: 5px; }
        .btn-green { background: #28a745; color: white; border: none; }
        .btn-blue { background: #007bff; color: white; border: none; }
        .btn-red { color: red; text-decoration: none; font-size: 12px; }
    </style>
</head>
<body>

    <h1>📊 ระบบเช็กค่าแคลอรี่ (Debug Mode)</h1>

    <div class="box">
        <h2>สรุป: กินเข้า <?php echo $total_in; ?> - เผาผลาญ <?php echo $total_out; ?> = คงเหลือ <?php echo ($total_in - $total_out); ?> kcal</h2>
    </div>

    <div class="flex">
        <div class="box" style="flex:1">
            <h3>🍽️ เพิ่มรายการกิน</h3>
            <form action="index.php" method="POST">
                <input type="text" name="food_name" placeholder="ชื่ออาหาร" required style="width:90%; padding:8px;"><br>
                <input type="number" name="calories" placeholder="แคลอรี่" required style="width:90%; padding:8px; margin-top:5px;"><br>
                <button type="submit" name="add_food" class="btn-green">บันทึกอาหาร</button>
            </form>
            
            <table>
                <tr><th>รายการ</th><th>แคล</th><th>ลบ</th></tr>
                <?php
                $data = mysqli_query($conn, "SELECT * FROM food_logs ORDER BY id DESC");
                while($row = mysqli_fetch_assoc($data)) {
                    echo "<tr>
                            <td>{$row['food_name']}</td>
                            <td>{$row['calories']}</td>
                            <td><a href='?del_f={$row['id']}' class='btn-red'>ลบ</a></td>
                          </tr>";
                }
                ?>
            </table>
        </div>

        <div class="box" style="flex:1">
            <h3>🏃 เพิ่มการออกกำลังกาย</h3>
            <form action="index.php" method="POST">
                <input type="text" name="activity" placeholder="กิจกรรม" required style="width:90%; padding:8px;"><br>
                <input type="number" name="burn_calories" placeholder="เผาผลาญ" required style="width:90%; padding:8px; margin-top:5px;"><br>
                <button type="submit" name="add_workout" class="btn-blue">บันทึกการเผาผลาญ</button>
            </form>

            <table>
                <tr><th>กิจกรรม</th><th>เผา</th><th>ลบ</th></tr>
                <?php
                $data = mysqli_query($conn, "SELECT * FROM workout_logs ORDER BY id DESC");
                while($row = mysqli_fetch_assoc($data)) {
                    echo "<tr>
                            <td>{$row['activity']}</td>
                            <td>{$row['burn_calories']}</td>
                            <td><a href='?del_w={$row['id']}' class='btn-red'>ลบ</a></td>
                          </tr>";
                }
                ?>
            </table>
        </div>
    </div>

</body>
</html>