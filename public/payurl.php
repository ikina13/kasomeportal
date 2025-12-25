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
    // First, get payment details to check if payment_type and class_id columns exist
    $sqlGetPayment = "SELECT id, user_id, video_id, 
                      COALESCE(class_id, (SELECT class_id FROM tbl_practical_video WHERE id = video_id)) as class_id,
                      COALESCE(payment_type, 'purchase') as payment_type, created_by, amount
                      FROM tbl_payment 
                      WHERE TransactionToken = :TransactionToken AND status = 'pending'";
    
    $stmtGet = $pdo->prepare($sqlGetPayment);
    $stmtGet->bindParam(':TransactionToken', $TransactionToken, PDO::PARAM_STR);
    $stmtGet->execute();
    $paymentInfo = $stmtGet->fetch(PDO::FETCH_ASSOC);
    
    if (!$paymentInfo) {
        throw new Exception("Payment not found or already processed");
    }
    
    // Update payment status
    $sqlUpdatePayment = "UPDATE tbl_payment 
                         SET TransID = :TransID, 
                             CCDapproval = :CCDapproval, 
                             PnrID = :PnrID, 
                             status = 'settled',
                             updated_at = NOW(),
                             expired_date = CASE 
                                 WHEN COALESCE(payment_type, 'purchase') = 'subscription' THEN NOW() + INTERVAL '90 days'
                                 ELSE NOW() + INTERVAL '30 days'
                             END
                         WHERE TransactionToken = :TransactionToken AND status = 'pending'
                         RETURNING id";

    $stmtUpdate = $pdo->prepare($sqlUpdatePayment);
    $stmtUpdate->bindParam(':TransID', $TransID, PDO::PARAM_STR);
    $stmtUpdate->bindParam(':CCDapproval', $CCDapproval, PDO::PARAM_STR);
    $stmtUpdate->bindParam(':PnrID', $PnrID, PDO::PARAM_STR);
    $stmtUpdate->bindParam(':TransactionToken', $TransactionToken, PDO::PARAM_STR);
    $stmtUpdate->execute();

    // Fetch the updated payment ID
    $updatedPayment = $stmtUpdate->fetch(PDO::FETCH_ASSOC);

    // 5. CHECK IF A PAYMENT WAS ACTUALLY UPDATED
    if ($updatedPayment && $paymentInfo) {
        $paymentType = $paymentInfo['payment_type'] ?? 'purchase';
        $classId = $paymentInfo['class_id'] ?? null;
        $paymentDetails = array_merge($paymentInfo, ['id' => $updatedPayment['id']]);
        
        // Hardcoding 40 for created_by and updated_by as previously requested
        $creator_id = 40; 
        $updater_id = 40;

        if ($paymentType === 'subscription' && $classId) {
            // 6a. CREATE OR UPDATE CLASS SUBSCRIPTION (3 months = 90 days)
            // Check if subscription already exists
            $sqlCheckSubscription = "SELECT id FROM tbl_class_subscriptions 
                                     WHERE user_id = :user_id AND class_id = :class_id AND status = 'active'";
            $stmtCheck = $pdo->prepare($sqlCheckSubscription);
            $stmtCheck->bindParam(':user_id', $paymentDetails['user_id'], PDO::PARAM_INT);
            $stmtCheck->bindParam(':class_id', $classId, PDO::PARAM_INT);
            $stmtCheck->execute();
            $existingSubscription = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            
            $subscriptionAmount = isset($paymentInfo['amount']) ? $paymentInfo['amount'] : 3000;
            
            if ($existingSubscription) {
                // Update existing subscription - extend end date by 90 days from now
                $sqlUpdateSubscription = "UPDATE tbl_class_subscriptions
                                          SET end_date = NOW() + INTERVAL '90 days',
                                              status = 'active',
                                              updated_at = NOW(),
                                              updated_by = :updated_by
                                          WHERE id = :subscription_id";
                $stmtUpdateSub = $pdo->prepare($sqlUpdateSubscription);
                $stmtUpdateSub->bindParam(':subscription_id', $existingSubscription['id'], PDO::PARAM_INT);
                $stmtUpdateSub->bindParam(':updated_by', $updater_id, PDO::PARAM_INT);
                $stmtUpdateSub->execute();
            } else {
                // Create new subscription
                $sqlInsertSubscription = "INSERT INTO tbl_class_subscriptions
                                            (user_id, class_id, amount, start_date, end_date, status, created_at, created_by, updated_at, updated_by)
                                        VALUES
                                            (:user_id, :class_id, :amount, NOW(), NOW() + INTERVAL '90 days', 'active', NOW(), :created_by, NOW(), :updated_by)";

                $stmtSubscription = $pdo->prepare($sqlInsertSubscription);
                $stmtSubscription->bindParam(':user_id', $paymentDetails['user_id'], PDO::PARAM_INT);
                $stmtSubscription->bindParam(':class_id', $classId, PDO::PARAM_INT);
                $stmtSubscription->bindParam(':amount', $subscriptionAmount, PDO::PARAM_INT);
                $stmtSubscription->bindParam(':created_by', $creator_id, PDO::PARAM_INT);
                $stmtSubscription->bindParam(':updated_by', $updater_id, PDO::PARAM_INT);
                
                $stmtSubscription->execute();
            }
        } else {
            // 6b. INSERT THE NEW ACCESS RECORD (for regular purchase - 30 days)
            $sqlInsertAccess = "INSERT INTO tbl_user_course_access
                                    (user_id, video_id, payment_id, status, access_start_date, access_end_date, created_at, created_by, updated_at, updated_by)
                                VALUES
                                    (:user_id, :video_id, :payment_id, 'active', NOW(), NOW() + INTERVAL '30 days', NOW(), :created_by, NOW(), :updated_by)";

            $stmtInsert = $pdo->prepare($sqlInsertAccess);
            $stmtInsert->bindParam(':user_id', $paymentDetails['user_id'], PDO::PARAM_INT);
            $stmtInsert->bindParam(':video_id', $paymentDetails['video_id'], PDO::PARAM_INT);
            $stmtInsert->bindParam(':payment_id', $paymentDetails['id'], PDO::PARAM_INT);
            $stmtInsert->bindParam(':created_by', $creator_id, PDO::PARAM_INT);
            $stmtInsert->bindParam(':updated_by', $updater_id, PDO::PARAM_INT);
            
            $stmtInsert->execute();
        }

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
