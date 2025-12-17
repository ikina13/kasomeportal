<?php
// Replace with your remote PostgreSQL database credentials
$host = '45.79.205.240';
$port = '5432';
$database = 'kasome_stage_db';
$username = 'postgres';
$password = 'kasome@2020';
$pdo = null; // Initialize pdo variable

try {
    // 1. ESTABLISH DATABASE CONNECTION
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$database;user=$username;password=$password");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. EXTRACT DATA FROM DPO WEBHOOK/CALLBACK
    $TransID = $_GET['TransID'];
    $CCDapproval = $_GET['CCDapproval'];
    $PnrID = $_GET['PnrID'];
    $TransactionToken = $_GET['TransactionToken'];

    // 3. BEGIN DATABASE TRANSACTION
    $pdo->beginTransaction();

    // 4. UPDATE THE PAYMENT and GET ITS DETAILS
    // We add the expired_date and updated_at fields here.
    $sqlUpdatePayment = "UPDATE tbl_payment 
                         SET TransID = :TransID, 
                             CCDapproval = :CCDapproval, 
                             PnrID = :PnrID, 
                             status = 'settled',
                             updated_at = NOW(),
                             expired_date = NOW() + INTERVAL '30 days' -- Adds 30 days
                         WHERE TransactionToken = :TransactionToken AND status = 'pending'
                         RETURNING id, user_id, video_id, created_by";

    $stmtUpdate = $pdo->prepare($sqlUpdatePayment);
    $stmtUpdate->bindParam(':TransID', $TransID, PDO::PARAM_STR);
    $stmtUpdate->bindParam(':CCDapproval', $CCDapproval, PDO::PARAM_STR);
    $stmtUpdate->bindParam(':PnrID', $PnrID, PDO::PARAM_STR);
    $stmtUpdate->bindParam(':TransactionToken', $TransactionToken, PDO::PARAM_STR);
    $stmtUpdate->execute();

    // Fetch the updated payment details
    $paymentDetails = $stmtUpdate->fetch(PDO::FETCH_ASSOC);

    // 5. CHECK IF A PAYMENT WAS ACTUALLY UPDATED
    if ($paymentDetails) {
        // A payment was found and updated. Now, create the access record.

        // 6. INSERT THE NEW ACCESS RECORD
        $sqlInsertAccess = "INSERT INTO tbl_user_course_access
                                (user_id, video_id, payment_id, status, access_start_date, access_end_date, created_at, created_by, updated_at, updated_by)
                            VALUES
                                (:user_id, :video_id, :payment_id, 'active', NOW(), NOW() + INTERVAL '30 days', NOW(), :created_by, NOW(), :updated_by)";

        $stmtInsert = $pdo->prepare($sqlInsertAccess);

        // Hardcoding 40 for created_by and updated_by as previously requested
        $creator_id = 40; 
        $updater_id = 40;

        $stmtInsert->bindParam(':user_id', $paymentDetails['user_id'], PDO::PARAM_INT);
        $stmtInsert->bindParam(':video_id', $paymentDetails['video_id'], PDO::PARAM_INT);
        $stmtInsert->bindParam(':payment_id', $paymentDetails['id'], PDO::PARAM_INT);
        $stmtInsert->bindParam(':created_by', $creator_id, PDO::PARAM_INT);
        $stmtInsert->bindParam(':updated_by', $updater_id, PDO::PARAM_INT);
        
        $stmtInsert->execute();

    } else {
        // No rows were updated, possibly a duplicate webhook call.
        // Log if needed, then continue.
    }
    
    // 7. COMMIT THE TRANSACTION
    $pdo->commit();

} catch (PDOException $e) {
    // 8. ROLLBACK ON ERROR
    if ($pdo && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Payment Processing Error: ' . $e->getMessage());
    echo 'Error: ' . $e->getMessage();
} finally {
    // Close the database connection
    $pdo = null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success</title>
</head>
<body>
    <h1>Payment Successful</h1>
    <p>Thank you for your purchase! Your access has been granted.</p>
</body>
</html>
