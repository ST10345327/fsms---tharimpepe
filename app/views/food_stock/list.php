<?php
$pageTitle = 'Food Stock';

function fsmsStockCategory(string $itemName): string
{
    $name = strtolower($itemName);
    if (str_contains($name, 'rice') || str_contains($name, 'maize')) {
        return 'Grains';
    }
    if (str_contains($name, 'bean')) {
        return 'Legumes';
    }
    if (str_contains($name, 'oil')) {
        return 'Oils';
    }
    if (str_contains($name, 'sugar')) {
        return 'Sweeteners';
    }
    if (str_contains($name, 'salt')) {
        return 'Spices';
    }
    return 'General';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Stock - FSMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/fsms-ui.css">
    <style>
        .stock-page {
            padding: 32px 30px;
        }

        .stock-alert {
            align-items: center;
            background: #fff7ed;
            border: 1px solid #fdba74;
            border-radius: 14px;
            color: #ff4d00;
            display: flex;
            gap: 18px;
            margin-bottom: 30px;
            padding: 24px 22px;
        }

        .stock-alert i {
            font-size: 28px;
        }

        .stock-alert strong {
            color: #b43200;
            font-size: 20px;
            font-weight: 700;
        }

        .stock-alert p {
            color: #ff4d00;
            font-size: 16px;
            margin: 4px 0 0;
        }

        .stock-toolbar {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 30px;
        }

        .stock-toolbar .btn {
            background: #1b3a5c;
            border: 0;
            border-radius: 10px;
            color: #fff;
            font-size: 20px;
            min-height: 50px;
            padding: 10px 24px;
        }

        .stock-card {
            background: #fff;
            border: 1px solid #dfe3e8;
            border-radius: 14px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.16);
            overflow: hidden;
        }

        .stock-table {
            margin: 0;
        }

        .stock-table thead th {
            background: #f8fafc;
            color: #334155;
            font-size: 14px;
            font-weight: 800;
            padding: 18px 30px;
        }

        .stock-table tbody td {
            border-color: #e5e7eb;
            color: #1f2a44;
            font-size: 18px;
            padding: 23px 30px;
            vertical-align: middle;
        }

        .stock-table tbody tr.low-stock-row {
            background: #fff1f2;
        }

        .status-pill {
            border-radius: 999px;
            display: inline-flex;
            font-size: 14px;
            padding: 6px 11px;
        }

        .status-pill.ok {
            background: #dcfce7;
            color: #008f35;
        }

        .status-pill.low {
            background: #fee2e2;
            border: 1px solid #fca5a5;
            color: #ff1f1f;
        }

        .row-actions {
            display: flex;
            gap: 18px;
        }

        .row-actions a {
            font-size: 18px;
            text-decoration: none;
        }

        .row-actions .edit {
            color: #0d6efd;
        }

        .row-actions .delete {
            color: #ff1f1f;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . "/../includes/navbar.php"; ?>

    <main class="container-fluid stock-page">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if (!empty($lowStockItems)): ?>
            <section class="stock-alert">
                <i class="fas fa-triangle-exclamation" aria-hidden="true"></i>
                <div>
                    <strong><?php echo count($lowStockItems); ?> items are running low on stock</strong>
                    <p>Please reorder soon to avoid shortages</p>
                </div>
            </section>
        <?php endif; ?>

        <div class="stock-toolbar">
            <?php if (function_exists('rbacCan') && rbacCan('food_stock')): ?>
            <a href="FoodStockController.php?action=create" class="btn">
                <i class="fas fa-plus me-2" aria-hidden="true"></i>Add Stock Item
            </a>
            <?php endif; ?>
        </div>

        <section class="stock-card">
            <table class="table stock-table">
                <thead>
                    <tr>
                        <th>ITEM NAME</th>
                        <th>CATEGORY</th>
                        <th>QUANTITY</th>
                        <th>UNIT</th>
                        <th>MIN THRESHOLD</th>
                        <th>EXPIRY DATE</th>
                        <th>STATUS</th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($stockItems)): ?>
                        <?php foreach ($stockItems as $item): ?>
                            <?php
                            $quantity = (int)$item['Quantity'];
                            $threshold = 5;
                            $isLowStock = $quantity <= $threshold;
                            $expiry = !empty($item['ExpiryDate']) ? date('Y-m-d', strtotime($item['ExpiryDate'])) : 'N/A';
                            ?>
                            <tr class="<?php echo $isLowStock ? 'low-stock-row' : ''; ?>">
                                <td><?php echo htmlspecialchars($item['ItemName']); ?></td>
                                <td><?php echo htmlspecialchars(fsmsStockCategory($item['ItemName'])); ?></td>
                                <td><?php echo $quantity; ?></td>
                                <td><?php echo htmlspecialchars($item['Unit'] ?? 'units'); ?></td>
                                <td><?php echo $threshold; ?></td>
                                <td><?php echo htmlspecialchars($expiry); ?></td>
                                <td>
                                    <span class="status-pill <?php echo $isLowStock ? 'low' : 'ok'; ?>">
                                        <?php echo $isLowStock ? 'LOW STOCK' : 'OK'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="row-actions">
                                        <a class="edit" href="FoodStockController.php?action=edit&id=<?php echo (int)$item['FoodStockID']; ?>" aria-label="Edit <?php echo htmlspecialchars($item['ItemName']); ?>">
                                            <i class="far fa-pen-to-square" aria-hidden="true"></i>
                                        </a>
                                        <?php if (function_exists('rbacCan') && rbacCan('food_stock.delete')): ?>
                                        <a class="delete" href="FoodStockController.php?action=delete&id=<?php echo (int)$item['FoodStockID']; ?>" aria-label="Delete <?php echo htmlspecialchars($item['ItemName']); ?>">
                                            <i class="far fa-trash-can" aria-hidden="true"></i>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">No food stock items found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
