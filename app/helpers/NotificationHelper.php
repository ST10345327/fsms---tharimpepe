<?php
require_once __DIR__ . "/../../config/database.php";

class NotificationHelper {
    private $pdo;
    private $userId;
    private $role;

    public function __construct($userId, $role) {
        global $pdo;
        $this->pdo = $pdo;
        if (!$this->pdo) {
            $db = new Database();
            $this->pdo = $db->connect();
        }
        $this->userId = $userId;
        $this->role = $role;
    }

    public function getNotifications() {
        $notifications = [];

        if ($this->role === 'admin') {
            $s = $this->pdo->query("SELECT COUNT(*) as c FROM Users WHERE Status = 'pending'");
            $pendingUsers = (int)$s->fetch()['c'];
            if ($pendingUsers > 0) {
                $notifications[] = [
                    'icon' => 'fas fa-user-clock',
                    'text' => "$pendingUsers user(s) pending approval",
                    'link' => '../controllers/UserController.php?action=list&status=pending',
                    'type' => 'warning'
                ];
            }
        }

        if (in_array($this->role, ['admin', 'staff'])) {
            $s = $this->pdo->query("SELECT COUNT(*) as c FROM Donations WHERE DonationDate >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
            $newDonations = (int)$s->fetch()['c'];
            if ($newDonations > 0) {
                $notifications[] = [
                    'icon' => 'fas fa-gift',
                    'text' => "$newDonations new donation(s) this week",
                    'link' => '../controllers/ReportsController.php?action=donations',
                    'type' => 'info'
                ];
            }

            $s = $this->pdo->query("SELECT COUNT(*) as c FROM FoodStock WHERE ExpiryDate < CURDATE()");
            $expired = (int)$s->fetch()['c'];
            if ($expired > 0) {
                $notifications[] = [
                    'icon' => 'fas fa-exclamation-triangle',
                    'text' => "$expired item(s) expired in stock",
                    'link' => '../controllers/ReportsController.php?action=food_stock',
                    'type' => 'danger'
                ];
            }

            $s = $this->pdo->query("SELECT COUNT(*) as c FROM FoodStock WHERE Quantity <= 5 AND (ExpiryDate >= CURDATE() OR ExpiryDate IS NULL)");
            $lowStock = (int)$s->fetch()['c'];
            if ($lowStock > 0) {
                $notifications[] = [
                    'icon' => 'fas fa-boxes',
                    'text' => "$lowStock item(s) low in stock",
                    'link' => '../controllers/ReportsController.php?action=food_stock',
                    'type' => 'warning'
                ];
            }
        }

        $s = $this->pdo->prepare("SELECT COUNT(*) as c FROM Messages WHERE RecipientID = ? AND IsRead = 0");
        $s->execute([$this->userId]);
        $unreadMsgs = (int)$s->fetch()['c'];
        if ($unreadMsgs > 0) {
            $notifications[] = [
                'icon' => 'fas fa-envelope',
                'text' => "$unreadMsgs unread message(s)",
                'link' => '../controllers/MessageController.php?action=inbox',
                'type' => 'primary'
            ];
        }

        return $notifications;
    }

    public function getTotalCount() {
        $s = $this->pdo->query("SELECT COUNT(*) as c FROM Users WHERE Status = 'pending'");
        $count = (int)$s->fetch()['c'];

        $s = $this->pdo->query("SELECT COUNT(*) as c FROM Donations WHERE DonationDate >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
        $count += (int)$s->fetch()['c'];

        $s = $this->pdo->query("SELECT COUNT(*) as c FROM FoodStock WHERE ExpiryDate < CURDATE() OR Quantity <= 5");
        $count += (int)$s->fetch()['c'];

        $s = $this->pdo->prepare("SELECT COUNT(*) as c FROM Messages WHERE RecipientID = ? AND IsRead = 0");
        $s->execute([$this->userId]);
        $count += (int)$s->fetch()['c'];

        return $count;
    }
}
