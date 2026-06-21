<?php
$pageTitle = 'Food Stock';
function fsmsStockCategory(string $itemName): string {
    $name = strtolower($itemName);
    if (str_contains($name, 'rice') || str_contains($name, 'maize')) return 'Grains';
    if (str_contains($name, 'bean')) return 'Legumes';
    if (str_contains($name, 'oil')) return 'Oils';
    if (str_contains($name, 'sugar')) return 'Sweeteners';
    if (str_contains($name, 'salt')) return 'Spices';
    return 'General';
}
require_once __DIR__ . "/../includes/layout-header.php";
?>
<style>
    .stock-alert {
        background: rgba(220, 38, 38, 0.05);
        border: 1px solid rgba(220, 38, 38, 0.2);
        border-radius: 12px;
        color: var(--fsms-red);
        padding: 16px 20px;
        margin-bottom: 24px;
    }
    .stock-card {
        background: white;
        border-radius: 16px;
        box-shadow: var(--fsms-shadow);
        border: 1px solid var(--fsms-border);
        overflow: hidden;
        width: 100%;
    }
    .stock-table thead th {
        background: #f8fafc;
        color: var(--fsms-muted);
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 16px 20px;
    }
    .stock-table tbody td {
        padding: 16px 20px;
        font-size: 14px;
        color: var(--fsms-text);
    }
    .status-pill {
        font-size: 11px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 20px;
    }
    .status-pill.ok { background: rgba(29, 158, 117, 0.1); color: var(--fsms-green); }
    .status-pill.low { background: rgba(220, 38, 38, 0.1); color: var(--fsms-red); }
</style>

<!-- Page Header -->
<div class="fsms-page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-boxes-stacked me-2"></i> Food Stock</h1>
            <p class="mb-0 mt-1 opacity-75">Inventory management and distribution tracking</p>
        </div>
        <a href="FoodStockController.php?action=create" class="btn btn-light text-success fw-bold"><i class="fas fa-plus"></i> Add Stock</a>
    </div>
</div>

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

<div class="mb-3">
    <a href="FoodStockController.php?action=create" class="btn btn-primary"><i class="fas fa-plus me-2" aria-hidden="true"></i>Add Stock Item</a>
</div>

<section class="stock-card">
    <div class="table-responsive">
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
                            <td><span class="status-pill <?php echo $isLowStock ? 'low' : 'ok'; ?>"><?php echo $isLowStock ? 'LOW STOCK' : 'OK'; ?></span></td>
                            <td>
                                <div class="row-actions" style="display:flex;gap:12px;">
                                    <a href="FoodStockController.php?action=edit&id=<?php echo (int)$item['FoodStockID']; ?>" aria-label="Edit"><i class="far fa-pen-to-square" style="color:#0d6efd;"></i></a>
                                    <a href="FoodStockController.php?action=delete&id=<?php echo (int)$item['FoodStockID']; ?>" aria-label="Delete"><i class="far fa-trash-can" style="color:#dc2626;"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="8" class="text-center text-muted py-5">No food stock items found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require_once __DIR__ . "/../includes/layout-footer.php"; ?>