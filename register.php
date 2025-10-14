<?php
// 1. กำหนด Webhook URL ของ n8n
// **สำคัญ:** แทนที่ URL ด้านล่างด้วย Production Webhook URL จริงของ Workflow #1 ของคุณ
$n8n_webhook_url = 'http://localhost:5678/webhook/smart-insight/register';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 2. ตรวจสอบและรับข้อมูลจากฟอร์ม
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $province = filter_input(INPUT_POST, 'province', FILTER_SANITIZE_STRING);
    $channel = filter_input(INPUT_POST, 'channel', FILTER_SANITIZE_STRING);
    
    // 3. จัดเตรียมข้อมูลในรูปแบบ JSON Payload ตามที่ n8n Webhook ต้องการ
    $payload = [
        'email' => $email,
        'province' => $province,
        'channel' => $channel,
    ];

    // 4. ส่งข้อมูลไปยัง n8n Webhook
    $ch = curl_init($n8n_webhook_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen(json_encode($payload))
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // 5. จัดการผลลัพธ์ (Response) จาก n8n
    if ($http_code === 200 || $http_code === 201) {
        $message = "🎉 สมัครสมาชิกสำเร็จ! ข้อมูลของคุณถูกส่งไปยังระบบแล้ว.";
        $message_type = 'success';
    } else {
        // หาก n8n มีปัญหา, Webhook อาจจะตอบกลับด้วย HTTP Code อื่นๆ
        $message = "❌ เกิดข้อผิดพลาดในการส่งข้อมูล: กรุณาลองใหม่อีกครั้ง (HTTP Code: " . $http_code . ")";
        $message_type = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>สมัครสมาชิก Smart Thailand Insight</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; }
        .container { max-width: 500px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #333; }
        label { display: block; margin-bottom: 8px; font-weight: bold; }
        input[type="email"], input[type="text"], select { width: 100%; padding: 10px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background-color: #007bff; color: white; padding: 12px 20px; border: none; border-radius: 4px; cursor: pointer; width: 100%; font-size: 16px; }
        button:hover { background-color: #0056b3; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 10px; margin-bottom: 20px; border-radius: 4px; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 10px; margin-bottom: 20px; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>สมัครสมาชิก Smart Thailand Insight</h2>
        
        <?php if ($message): ?>
            <div class="<?= $message_type ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <label for="email">อีเมล (Gmail)</label>
            <input type="email" id="email" name="email" required placeholder="name@gmail.com">

            <label for="province">จังหวัดที่สนใจ</label>
            <input type="text" id="province" name="province" required placeholder="เช่น Bangkok หรือ Chiang Mai">

            <label for="channel">ช่องทางการแจ้งเตือนที่ต้องการ</label>
            <select id="channel" name="channel" required>
                <option value="">-- เลือกช่องทาง --</option>
                <option value="email">Email</option>
                <option value="line">Line</option>
            </select>

            <button type="submit">ยืนยันการสมัครสมาชิก</button>
        </form>
    </div>
</body>
</html>