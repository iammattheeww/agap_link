<?php
require_once dirname(__DIR__) . "/config/init.php";

require_once MODEL_PATH . 'User.php';

// CHECK IF USER IS LOGGED IN
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/view/auth/index.php");
    exit();
}

$user = new User();
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'update_profile':
        update_profile();
        break;

    case 'change_password':
        change_password();
        break;

    case 'delete_account':
        delete_account();
        break;

    default:
        $_SESSION['error'] = "Invalid action!";
        header("Location: " . BASE_URL . "/view/user_module/profile.php");
        exit();
}

// UPDATE USER PROFILE INFORMATION
function update_profile()
{
    global $user;

    // GET AND SANITIZE INPUT
    $firstName = trim($_POST['first_name']);
    $middleInitial = isset($_POST['middle_initial']) && !empty(trim($_POST['middle_initial']))
        ? strtoupper(trim($_POST['middle_initial']))
        : null;
    $lastName = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phoneNumber = trim($_POST['phone_number']);

    // VALIDATE REQUIRED FIELDS
    if (empty($firstName) || empty($lastName) || empty($email) || empty($phoneNumber)) {
        $_SESSION['error'] = "Please fill in all required fields!";
        header("Location: " . BASE_URL . "/view/user_module/profile.php");
        exit();
    }

    // VALIDATE MIDDLE INITIAL FORMAT (IF PROVIDED)
    if ($middleInitial !== null && strlen($middleInitial) > 5) {
        $_SESSION['error'] = "Middle initial should be 1-5 characters only!";
        header("Location: " . BASE_URL . "/view/user_module/profile.php");
        exit();
    }

    // VALIDATE EMAIL FORMAT
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format!";
        header("Location: " . BASE_URL . "/view/user_module/profile.php");
        exit();
    }

    // VALIDATE PHONE NUMBER FORMAT (Philippine mobile number)
    if (!preg_match('/^09[0-9]{9}$/', $phoneNumber)) {
        $_SESSION['error'] = "Invalid phone number format! Use 09XXXXXXXXX";
        header("Location: " . BASE_URL . "/view/user_module/profile.php");
        exit();
    }

    // CHECK IF EMAIL ALREADY EXISTS (for different user)
    $currentEmail = $user->get_user_email($_SESSION['user_id']);
    if ($email !== $currentEmail && $user->email_exists($email)) {
        $_SESSION['error'] = "This email is already registered to another account!";
        header("Location: /agap_link/view/user_module/profile.php");
        exit();
    }

    // UPDATE USER INFORMATION
    try {
        $result = $user->update_user(
            $_SESSION['user_id'],
            $firstName,
            $middleInitial,
            $lastName,
            $email,
            $phoneNumber
        );

        if ($result) {
            // UPDATE SESSION VARIABLES
            $_SESSION['first_name'] = $firstName;
            $_SESSION['middle_initial'] = $middleInitial;
            $_SESSION['last_name'] = $lastName;
            $_SESSION['email'] = $email;
            $_SESSION['phone_number'] = $phoneNumber;

            // UPDATE FULL NAME IN SESSION
            if ($middleInitial) {
                $_SESSION['user_name'] = $firstName . ' ' . $middleInitial . '. ' . $lastName;
            } else {
                $_SESSION['user_name'] = $firstName . ' ' . $lastName;
            }

            $_SESSION['success'] = "Profile updated successfully!";
            header("Location: " . BASE_URL . "/view/user_module/profile.php");
            exit();
        } else {
            $_SESSION['error'] = "Failed to update profile. Please try again.";
            header("Location: " . BASE_URL . "/view/user_module/profile.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Update failed: " . $e->getMessage();
        header("Location: " . BASE_URL . "/view/user_module/profile.php");
        exit();
    }
}

// CHANGE USER PASSWORD
function change_password()
{
    global $user;

    // GET AND SANITIZE INPUT
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    // VALIDATE REQUIRED FIELDS
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $_SESSION['error'] = "Please fill in all password fields!";
        header("Location: /agap_link/view/user_module/profile.php");
        exit();
    }

    // VALIDATE NEW PASSWORD LENGTH
    if (strlen($newPassword) < 8) {
        $_SESSION['error'] = "New password must be at least 8 characters long!";
        header("Location: /agap_link/view/user_module/profile.php");
        exit();
    }

    // VALIDATE PASSWORD MATCH
    if ($newPassword !== $confirmPassword) {
        $_SESSION['error'] = "New passwords do not match!";
        header("Location: /agap_link/view/user_module/profile.php");
        exit();
    }
    
    // HASH NEW PASSWORD
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // UPDATE PASSWORD IN DATABASE
    try {
        $sql = "UPDATE users SET password_hash = :password WHERE user_id = :user_id";
        $conn = new PDO(
            "mysql:host=localhost;dbname=agap_link",
            "root",
            ""
        );
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([
            ':password' => $hashedPassword,
            ':user_id' => $_SESSION['user_id']
        ]);

        if ($result) {
            $_SESSION['success'] = "Password changed successfully!";
            header("Location: " . BASE_URL . "/view/user_module/profile.php");
            exit();
        } else {
            $_SESSION['error'] = "Failed to change password. Please try again.";
            header("Location: " . BASE_URL . "/view/user_module/profile.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Password change failed: " . $e->getMessage();
        header("Location: " . BASE_URL . "/view/user_module/profile.php");
        exit();
    }
}

// DELETE USER ACCOUNT - UPDATED TO USE MODEL METHOD
function delete_account()
{
    global $user; // INSTANCE NI SIYA

    // VERIFY CONFIRMATION INPUT
    $confirmation = trim($_POST['delete_confirmation'] ?? '');

    if ($confirmation !== 'DELETE') {
        $_SESSION['error'] = "Account deletion cancelled. Confirmation text did not match.";
        header("Location: /agap_link/view/user_module/profile.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];

    try {
        // CONNECT TO DATABASE FOR PHOTO DELETION AND TRANSACTION
        $conn = new PDO(
            "mysql:host=localhost;dbname=agap_link",
            "root",
            ""
        );
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // BEGIN TRANSACTION
        $conn->beginTransaction();

        // GET USER'S REPORTS TO DELETE ASSOCIATED PHOTOS
        $getReportsQuery = "SELECT photo_path FROM reports WHERE user_id = :user_id AND photo_path IS NOT NULL";
        $getReportsStmt = $conn->prepare($getReportsQuery);
        $getReportsStmt->execute([':user_id' => $user_id]);
        $reports = $getReportsStmt->fetchAll(PDO::FETCH_ASSOC);

        // DELETE REPORT PHOTOS FROM FILESYSTEM
        foreach ($reports as $report) {
            $photo_path = __DIR__ . '/..' . $report['photo_path'];
            if (file_exists($photo_path)) {
                unlink($photo_path);
            }
        }

        // DELETE USER REPORTS (manually, before user deletion)
        $deleteReportsQuery = "DELETE FROM reports WHERE user_id = :user_id";
        $deleteReportsStmt = $conn->prepare($deleteReportsQuery);
        $deleteReportsStmt->execute([':user_id' => $user_id]);

        // COMMIT TRANSACTION
        $conn->commit();

        // NOW USE THE MODEL'S delete_user METHOD
        // This follows proper MVC pattern - Controller uses Model
        $deleteResult = $user->delete_user($user_id);

        if ($deleteResult) {
            // DESTROY SESSION
            $_SESSION = array();
            if (isset($_COOKIE[session_name()])) {
                setcookie(session_name(), '', time() - 3600, '/');
            }
            session_destroy();

            // START NEW SESSION FOR SUCCESS MESSAGE
            session_start();
            $_SESSION['success'] = "Your account has been successfully deleted. We're sorry to see you go.";

            // REDIRECT TO LANDING PAGE
            header("Location: /agap_link/index.php");
            exit();
        } else {
            throw new Exception("Failed to delete user from database.");
        }
    } catch (Exception $e) {
        // ROLLBACK ON ERROR
        if (isset($conn) && $conn->inTransaction()) {
            $conn->rollBack();
        }

        $_SESSION['error'] = "Failed to delete account: " . $e->getMessage();
        header("Location: " . BASE_URL . "/view/user_module/profile.php");
        exit();
    }
}
