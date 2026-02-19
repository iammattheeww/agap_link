<?php
require_once dirname(__DIR__) . '/config/agaplinkdb.php';

class Report
{
    private $conn;

    // CONSTRUCTOR TO INITIALIZE DATABASE CONNECTION
    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
    }

    // GET ALL CATEGORIES FOR DROPDOWNS (NEW)
    public function getAllCategories()
    {
        $sql = "SELECT category_id, name FROM categories ORDER BY name ASC";
        $q = $this->conn->query($sql);
        return $q->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserReports($user_id)
    {
        $sql = "SELECT 
                    r.*,
                    c.name as category_name,
                    a.name as agency_name,
                    CONCAT(
                        u.first_name, 
                        ' ', 
                        IFNULL(CONCAT(u.middle_initial, '. '), ''), 
                        u.last_name
                    ) AS reporter_name
                FROM reports r
                LEFT JOIN categories c ON r.category_id = c.category_id
                LEFT JOIN agencies a ON r.assigned_agency_id = a.agency_id
                LEFT JOIN users u ON r.user_id = u.user_id
                WHERE r.user_id = :user_id 
                ORDER BY r.created_at DESC";

        $q = $this->conn->prepare($sql);
        $q->execute(['user_id' => $user_id]);
        return $q->fetchAll(PDO::FETCH_ASSOC);
    }

    // GET USER REPORT STATISTICS
    public function getUserReportStats($user_id)
    {
        $sql = "SELECT 
                    COUNT(*) as total_reports,
                    SUM(CASE WHEN status = 'Resolved' THEN 1 ELSE 0 END) as resolved_count,
                    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending_count,
                    SUM(CASE WHEN status = 'Ongoing' THEN 1 ELSE 0 END) as ongoing_count
                FROM reports 
                WHERE user_id = :user_id";

        $q = $this->conn->prepare($sql);
        $q->execute(['user_id' => $user_id]);
        return $q->fetch(PDO::FETCH_ASSOC);
    }

    // CREATE NEW REPORT
    public function createReport($user_id, $category_id, $description, $address, $photo_path = null, $gps_lat = null, $gps_long = null, $priority = 'Medium', $assigned_agency_id = null)
    {
        $NOW = new DateTime('now', new DateTimeZone('Asia/Manila'));
        $NOW = $NOW->format('Y-m-d H:i:s');

        $sql = "INSERT INTO reports 
                (user_id, category_id, assigned_agency_id, description, address, photo_path, gps_lat, gps_long, priority, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', ?)";

        $stmt = $this->conn->prepare($sql);

        try {
            $this->conn->beginTransaction();
            $stmt->execute([
                $user_id,
                $category_id,
                $assigned_agency_id,
                $description,
                $address,
                $photo_path,
                $gps_lat,
                $gps_long,
                $priority,
                $NOW
            ]);
            $report_id = $this->conn->lastInsertId();
            $this->conn->commit();
            return $report_id;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    // GET SINGLE REPORT BY ID
    public function getReportById($report_id)
    {
        $sql = "SELECT 
                    r.*,
                    c.name as category_name,
                    a.name as agency_name,
                    CONCAT(
                        u.first_name, 
                        ' ', 
                        IFNULL(CONCAT(u.middle_initial, '. '), ''), 
                        u.last_name
                    ) AS reporter_name,
                    u.email as reporter_email,
                    u.phone_number as reporter_phone
                FROM reports r
                LEFT JOIN categories c ON r.category_id = c.category_id
                LEFT JOIN agencies a ON r.assigned_agency_id = a.agency_id
                LEFT JOIN users u ON r.user_id = u.user_id
                WHERE r.report_id = :report_id";

        $q = $this->conn->prepare($sql);
        $q->execute(['report_id' => $report_id]);
        return $q->fetch(PDO::FETCH_ASSOC);
    }

    // UPDATE REPORT STATUS
    public function updateReportStatus($report_id, $new_status, $remarks = null)
    {
        $NOW = new DateTime('now', new DateTimeZone('Asia/Manila'));
        $NOW = $NOW->format('Y-m-d H:i:s');

        try {
            $this->conn->beginTransaction();

            // Update report status
            $sql = "UPDATE reports SET status = :status WHERE report_id = :report_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'status' => $new_status,
                'report_id' => $report_id
            ]);

            // Log the status change
            $log_sql = "INSERT INTO report_logs (report_id, status_change, remarks, timestamp) 
                        VALUES (:report_id, :status_change, :remarks, :timestamp)";
            $log_stmt = $this->conn->prepare($log_sql);
            $log_stmt->execute([
                'report_id' => $report_id,
                'status_change' => $new_status,
                'remarks' => $remarks,
                'timestamp' => $NOW
            ]);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    // GET REPORT LOGS/HISTORY
    public function getReportLogs($report_id)
    {
        $sql = "SELECT * FROM report_logs 
                WHERE report_id = :report_id 
                ORDER BY timestamp DESC";

        $q = $this->conn->prepare($sql);
        $q->execute(['report_id' => $report_id]);
        return $q->fetchAll(PDO::FETCH_ASSOC);
    }

    // GET ALL REPORTS (FOR ADMIN)
    public function getAllReports()
    {
        $sql = "SELECT 
                    r.*,
                    c.name as category_name,
                    a.name as agency_name,
                    CONCAT(
                        u.first_name, 
                        ' ', 
                        IFNULL(CONCAT(u.middle_initial, '. '), ''), 
                        u.last_name
                    ) AS reporter_name
                FROM reports r
                LEFT JOIN categories c ON r.category_id = c.category_id
                LEFT JOIN agencies a ON r.assigned_agency_id = a.agency_id
                LEFT JOIN users u ON r.user_id = u.user_id
                ORDER BY r.created_at DESC";

        $q = $this->conn->query($sql);
        return $q->fetchAll(PDO::FETCH_ASSOC);
    }

    // GET ALL REPORTS WITH OPTIONAL FILTERS (replaces raw query in admin_report.php)
    public function getFilteredReports($filterStatus = '', $filterCategory = '', $filterSearch = '')
    {
        $sql = "SELECT r.*, c.name AS category_name, a.name AS agency_name,
                       CONCAT(u.first_name, ' ', IFNULL(CONCAT(u.middle_initial, '. '), ''), u.last_name) AS full_name
                FROM reports r
                LEFT JOIN categories c ON r.category_id = c.category_id
                LEFT JOIN agencies   a ON r.assigned_agency_id = a.agency_id
                LEFT JOIN users      u ON r.user_id = u.user_id
                WHERE 1=1";

        $params = [];

        if ($filterStatus !== '') {
            $sql .= " AND r.status = ?";
            $params[] = $filterStatus;
        }

        if ($filterCategory !== '') {
            $sql .= " AND r.category_id = ?";
            $params[] = $filterCategory;
        }

        if ($filterSearch !== '') {
            $sql .= " AND (r.description LIKE ? OR r.address LIKE ? OR c.name LIKE ? OR CONCAT(u.first_name,' ',u.last_name) LIKE ?)";
            $like = "%{$filterSearch}%";
            $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like;
        }

        $sql .= " ORDER BY r.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // GET ALL AGENCIES
    public function getAllAgencies()
    {
        $stmt = $this->conn->query("SELECT agency_id, name FROM agencies ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
