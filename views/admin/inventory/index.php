<?php require __DIR__ . '/../layout-header.php'; ?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold m-0 text-dark">Inventory &amp; Stock Levels</h4>
        <p class="text-muted small m-0">Live stock counts, low stock alerts, and complete audit movement trail</p>
    </div>

    <!-- Filters & Search Form -->
    <form method="GET" action="<?= url('admin/inventory') ?>" class="d-flex flex-wrap gap-2">
        <select name="per_page" class="form-select form-select-sm" onchange="this.form.submit()" style="width: auto;">
            <option value="20" <?= ($perPage ?? 20) == 20 ? 'selected' : '' ?>>20 per page</option>
            <option value="30" <?= ($perPage ?? 20) == 30 ? 'selected' : '' ?>>30 per page</option>
            <option value="50" <?= ($perPage ?? 20) == 50 ? 'selected' : '' ?>>50 per page</option>
            <option value="100" <?= ($perPage ?? 20) == 100 ? 'selected' : '' ?>>100 per page</option>
        </select>

        <div class="input-group input-group-sm" style="width: 260px;">
            <input type="text" name="q" class="form-control" placeholder="Search product or SKU..." value="<?= e($search ?? '') ?>">
            <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
        </div>
    </form>
</div>

<div class="row g-4 mb-4">
    <!-- Live Inventory Table -->
    <div class="col-lg-8">
        <div class="admin-table-card">
            <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold m-0 text-dark d-flex align-items-center gap-2">
                    Current Stock by Product <span class="badge bg-secondary-subtle text-dark rounded-pill">Total: <?= $total ?? count($items) ?></span>
                </h6>
            </div>
            <div class="table-responsive">
                <table class="table admin-table align-middle">
                    <thead>
                        <tr>
                            <th style="width: 45px; text-align: center;">#</th>
                            <th>Product Name</th>
                            <th>SKU</th>
                            <th>Category</th>
                            <th>Current Stock</th>
                            <th class="text-end" style="width: 140px; min-width: 130px; white-space: nowrap;">Quick Adjust</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $idx => $it): ?>
                            <tr>
                                <td class="text-center text-muted fw-bold small"><?= $idx + 1 ?></td>
                                <td><strong class="small text-dark"><?= e($it['product_name']) ?></strong></td>
                                <td><code class="text-primary-emphasis bg-light px-2 py-1 rounded"><?= e($it['sku']) ?></code></td>
                                <td><span class="badge bg-light text-dark border"><?= e($it['category_name']) ?></span></td>
                                <td>
                                    <?php if ($it['stock'] <= 0): ?>
                                        <span class="stock-badge stock-badge-out">
                                            <i class="bi bi-x-circle-fill"></i> Out of Stock
                                        </span>
                                    <?php elseif ($it['stock'] <= $it['low_stock_threshold']): ?>
                                        <span class="stock-badge stock-badge-low">
                                            <i class="bi bi-exclamation-triangle-fill"></i> Low: <?= (int)$it['stock'] ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="stock-badge stock-badge-ok">
                                            <i class="bi bi-check-circle-fill"></i> <?= (int)$it['stock'] ?> in stock
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end text-nowrap" style="white-space: nowrap;">
                                    <button type="button" 
                                            class="btn-table-adjust btn-quick-adjust" 
                                            data-id="<?= (int)$it['product_id'] ?>" 
                                            data-name="<?= e($it['product_name']) ?>" 
                                            data-sku="<?= e($it['sku']) ?>" 
                                            data-stock="<?= (int)$it['stock'] ?>"
                                            title="Quick adjust stock for <?= e($it['product_name']) ?>">
                                        <i class="bi bi-pencil-square"></i> Adjust
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if (($totalPages ?? 1) > 1): ?>
                <div class="d-flex justify-content-between align-items-center p-3 border-top bg-light rounded-bottom-4">
                    <small class="text-muted">Showing page <?= $page ?> of <?= $totalPages ?> (Total <?= $total ?> products)</small>
                    <nav>
                        <ul class="pagination pagination-sm m-0">
                            <?php if ($page > 1): ?>
                                <li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">«</a></li>
                            <?php endif; ?>
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= $i == $page ? 'active' : '' ?>"><a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a></li>
                            <?php endfor; ?>
                            <?php if ($page < $totalPages): ?>
                                <li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">»</a></li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Stock Adjustment Box (Sidebar fallback) -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <h6 class="fw-bold mb-3 border-bottom pb-2 text-dark">
                <i class="bi bi-sliders me-1 text-primary"></i> Manual Stock Intake / Adjustment
            </h6>
            <form action="<?= url('admin/inventory/adjust') ?>" method="POST" id="adjustStockForm">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Select Product *</label>
                    <select name="product_id" id="adjProductSelect" class="form-select" required onchange="onSidebarProductChange(this)">
                        <?php foreach (($allProducts ?? $items) as $it): ?>
                            <option value="<?= $it['product_id'] ?>" data-stock="<?= (int)$it['stock'] ?>" data-sku="<?= e($it['sku']) ?>">
                                <?= e($it['sku']) ?> - <?= e($it['product_name']) ?> (<?= (int)$it['stock'] ?> in stock)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="small text-muted mt-1" id="sidebarStockNotice">
                        Current stock: <strong><?= !empty($items[0]['stock']) ? (int)$items[0]['stock'] : 0 ?> units</strong>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Stock Change (+/-) *</label>
                    <input type="number" name="change_amount" id="sidebarChangeInput" class="form-control" placeholder="e.g. 50 or -5" required>
                    <small class="text-muted" style="font-size: 0.75rem;">Positive number to add stock, negative to subtract.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Reason for Adjustment *</label>
                    <input type="text" name="reason" id="sidebarReasonInput" class="form-control" placeholder="e.g. Warehouse intake batch 4" required value="Warehouse intake batch">
                </div>
                <button type="submit" class="btn btn-afh-primary w-100 fw-bold shadow-sm py-2">
                    <i class="bi bi-arrow-repeat me-1"></i> Save Stock Movement
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Stock Audit Logs -->
<div class="admin-table-card">
    <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
        <h6 class="fw-bold m-0 text-dark d-flex align-items-center gap-2">
            Recent Inventory Logs &amp; Audit Trail <span class="badge bg-secondary-subtle text-dark rounded-pill"><?= count($logs) ?> logs</span>
        </h6>
    </div>
    <div class="table-responsive">
        <table class="table admin-table align-middle">
            <thead>
                <tr>
                    <th style="width: 45px; text-align: center;">#</th>
                    <th>Date &amp; Time</th>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Previous</th>
                    <th>Change</th>
                    <th>New Stock</th>
                    <th>Reason</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr><td colspan="8" class="text-center py-4 text-muted">No stock movement logs recorded.</td></tr>
                <?php else: ?>
                    <?php foreach ($logs as $idx => $log): ?>
                        <tr>
                            <td class="text-center text-muted fw-bold small"><?= $idx + 1 ?></td>
                            <td><small class="text-muted"><?= date('d M Y, h:i A', strtotime($log['created_at'])) ?></small></td>
                            <td><strong class="small"><?= e($log['product_name']) ?></strong></td>
                            <td><code><?= e($log['sku']) ?></code></td>
                            <td><?= (int)$log['previous_stock'] ?></td>
                            <td>
                                <span class="badge bg-<?= $log['change_amount'] > 0 ? 'success' : 'danger' ?>">
                                    <?= $log['change_amount'] > 0 ? '+' : '' ?><?= (int)$log['change_amount'] ?>
                                </span>
                            </td>
                            <td><strong><?= (int)$log['new_stock'] ?></strong></td>
                            <td><small class="text-muted"><?= e($log['reason']) ?></small></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Quick Stock Adjustment Modal Dialog -->
<div class="modal fade" id="quickAdjustModal" tabindex="-1" aria-labelledby="quickAdjustModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-bottom py-3 px-4 bg-light rounded-top-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="bi bi-boxes fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark m-0" id="quickAdjustModalLabel">Quick Stock Adjustment</h5>
                        <small class="text-muted" id="modalProductSku">SKU: ---</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= url('admin/inventory/adjust') ?>" method="POST" id="quickAdjustModalForm">
                <?= csrf_field() ?>
                <input type="hidden" name="product_id" id="modalProductId" value="">
                <input type="hidden" name="change_amount" id="modalChangeAmount" value="0">

                <div class="modal-body p-4">
                    <!-- Product Info Header -->
                    <div class="p-3 bg-light rounded-3 mb-3 border">
                        <strong class="text-dark d-block mb-1" id="modalProductName">Product Name</strong>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Current In-Stock:</span>
                            <span class="badge bg-dark fs-6 px-3 py-1" id="modalCurrentStock">0 units</span>
                        </div>
                    </div>

                    <!-- Adjustment Type: Add vs Deduct -->
                    <label class="form-label small fw-bold text-dark mb-1">Adjustment Type *</label>
                    <div class="btn-group w-100 mb-3" role="group">
                        <input type="radio" class="btn-check" name="modal_mode" id="modeAdd" value="add" checked onchange="calcModalNewStock()">
                        <label class="btn btn-outline-success fw-bold py-2" for="modeAdd">
                            <i class="bi bi-plus-circle me-1"></i> Add Stock (+)
                        </label>

                        <input type="radio" class="btn-check" name="modal_mode" id="modeSubtract" value="subtract" onchange="calcModalNewStock()">
                        <label class="btn btn-outline-danger fw-bold py-2" for="modeSubtract">
                            <i class="bi bi-dash-circle me-1"></i> Deduct Stock (-)
                        </label>
                    </div>

                    <!-- Quick Preset Quantity Chips -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted mb-1">Quick Presets</label>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="setPresetQty(5)">+5</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="setPresetQty(10)">+10</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="setPresetQty(25)">+25</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="setPresetQty(50)">+50</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="setPresetQty(100)">+100</button>
                        </div>
                    </div>

                    <!-- Quantity Input -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Quantity of Units *</label>
                        <input type="number" id="modalQtyInput" class="form-control form-control-lg fw-bold text-center" min="1" step="1" placeholder="e.g. 10" required oninput="calcModalNewStock()">
                    </div>

                    <!-- Calculated Result Live Badge -->
                    <div class="alert alert-light border rounded-3 p-3 mb-3 d-flex justify-content-between align-items-center">
                        <span class="small text-muted fw-semibold">New Resulting Stock:</span>
                        <strong class="fs-5 text-primary" id="modalNewStockCalc">--</strong>
                    </div>

                    <!-- Reason -->
                    <div class="mb-2">
                        <label class="form-label small fw-bold text-dark">Reason for Stock Movement *</label>
                        <select id="modalReasonSelect" class="form-select mb-2" onchange="onModalReasonSelectChange(this)">
                            <option value="New warehouse shipment intake">New warehouse shipment intake</option>
                            <option value="Restock from manufacturing unit">Restock from manufacturing unit</option>
                            <option value="Customer return restocked">Customer return restocked</option>
                            <option value="Damaged / defective stock written off">Damaged / defective stock written off</option>
                            <option value="Physical inventory audit correction">Physical inventory audit correction</option>
                            <option value="Sample / PR outreach allocation">Sample / PR outreach allocation</option>
                            <option value="__custom__">Other (Custom reason...)</option>
                        </select>
                        <input type="text" name="reason" id="modalReasonInput" class="form-control" value="New warehouse shipment intake" required placeholder="Enter specific reason">
                    </div>
                </div>

                <div class="modal-footer border-top py-3 px-4 bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-light px-4 py-2 rounded-pill fw-semibold border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-afh-primary px-4 py-2 rounded-pill fw-bold shadow-sm" id="modalSubmitBtn">
                        <i class="bi bi-check2-circle me-1"></i> Apply Stock Adjustment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentAdjustStock = 0;
let adjustModalInstance = null;

document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('quickAdjustModal');
    if (modalEl && window.bootstrap && window.bootstrap.Modal) {
        adjustModalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    }

    // Attach click listener to all Quick Adjust buttons
    document.querySelectorAll('.btn-quick-adjust').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const prodId = this.getAttribute('data-id');
            const prodName = this.getAttribute('data-name');
            const prodSku = this.getAttribute('data-sku');
            const prodStock = parseInt(this.getAttribute('data-stock'), 10) || 0;
            openQuickAdjustModal(prodId, prodName, prodSku, prodStock);
        });
    });

    // Handle Quick Adjust Modal Form Submit with Custom Confirmation
    const modalForm = document.getElementById('quickAdjustModalForm');
    if (modalForm) {
        modalForm.addEventListener('submit', function (e) {
            if (modalForm.dataset.confirmed === 'true') return;

            e.preventDefault();
            const qty = parseInt(document.getElementById('modalQtyInput').value, 10) || 0;
            const mode = document.querySelector('input[name="modal_mode"]:checked').value;
            const finalChange = (mode === 'subtract') ? -Math.abs(qty) : Math.abs(qty);
            
            if (qty <= 0) {
                alert('Please enter a valid quantity of units.');
                return;
            }

            document.getElementById('modalChangeAmount').value = finalChange;
            const pName = document.getElementById('modalProductName').textContent;
            const newCalcStock = Math.max(0, currentAdjustStock + finalChange);

            if (window.AforeConfirm) {
                window.AforeConfirm.ask({
                    type: mode === 'subtract' ? 'warning' : 'toggle',
                    title: 'Confirm Stock Movement',
                    message: 'Adjust stock for "' + pName + '" by ' + (finalChange > 0 ? '+' : '') + finalChange + ' units? (Stock will change from ' + currentAdjustStock + ' to ' + newCalcStock + ').',
                    confirmText: 'Yes, Apply Adjustment',
                    onConfirm: function () {
                        modalForm.dataset.confirmed = 'true';
                        modalForm.submit();
                    }
                });
            } else {
                modalForm.submit();
            }
        });
    }

    // Handle Sidebar Adjust Form Submit with Confirmation
    const sidebarForm = document.getElementById('adjustStockForm');
    if (sidebarForm) {
        sidebarForm.addEventListener('submit', function (e) {
            if (sidebarForm.dataset.confirmed === 'true') return;

            e.preventDefault();
            const changeVal = parseInt(document.getElementById('sidebarChangeInput').value, 10);
            if (!changeVal || isNaN(changeVal)) {
                alert('Please enter a non-zero stock change amount (+ or -).');
                return;
            }

            const sel = document.getElementById('adjProductSelect');
            const prodText = sel.options[sel.selectedIndex].text;

            if (window.AforeConfirm) {
                window.AforeConfirm.ask({
                    type: changeVal < 0 ? 'warning' : 'toggle',
                    title: 'Confirm Stock Adjustment',
                    message: 'Record movement of ' + (changeVal > 0 ? '+' : '') + changeVal + ' units for: ' + prodText + '?',
                    confirmText: 'Yes, Save Movement',
                    onConfirm: function () {
                        sidebarForm.dataset.confirmed = 'true';
                        sidebarForm.submit();
                    }
                });
            } else {
                sidebarForm.submit();
            }
        });
    }
});

function openQuickAdjustModal(id, name, sku, stock) {
    currentAdjustStock = parseInt(stock, 10) || 0;

    document.getElementById('modalProductId').value = id;
    document.getElementById('modalProductName').textContent = name;
    document.getElementById('modalProductSku').textContent = 'SKU: ' + sku;
    document.getElementById('modalCurrentStock').textContent = currentAdjustStock + ' units';
    
    // Reset inputs
    document.getElementById('modeAdd').checked = true;
    document.getElementById('modalQtyInput').value = '';
    document.getElementById('modalChangeAmount').value = '0';
    document.getElementById('modalNewStockCalc').textContent = currentAdjustStock + ' units';
    
    // Also sync sidebar select
    const sel = document.getElementById('adjProductSelect');
    if (sel) {
        sel.value = id;
        onSidebarProductChange(sel);
    }

    // Show modal
    if (!adjustModalInstance) {
        const modalEl = document.getElementById('quickAdjustModal');
        adjustModalInstance = new bootstrap.Modal(modalEl);
    }
    adjustModalInstance.show();

    setTimeout(function () {
        const inp = document.getElementById('modalQtyInput');
        if (inp) inp.focus();
    }, 400);
}

function setPresetQty(amount) {
    const inp = document.getElementById('modalQtyInput');
    inp.value = amount;
    calcModalNewStock();
}

function calcModalNewStock() {
    const qty = parseInt(document.getElementById('modalQtyInput').value, 10) || 0;
    const mode = document.querySelector('input[name="modal_mode"]:checked').value;
    const change = (mode === 'subtract') ? -Math.abs(qty) : Math.abs(qty);
    const newStock = Math.max(0, currentAdjustStock + change);

    document.getElementById('modalChangeAmount').value = change;
    
    const badge = document.getElementById('modalNewStockCalc');
    if (qty === 0) {
        badge.textContent = currentAdjustStock + ' units';
        badge.className = 'fs-5 text-muted';
    } else if (change > 0) {
        badge.textContent = newStock + ' units (+' + change + ')';
        badge.className = 'fs-5 text-success fw-bold';
    } else {
        badge.textContent = newStock + ' units (' + change + ')';
        badge.className = 'fs-5 text-danger fw-bold';
    }
}

function onModalReasonSelectChange(selectEl) {
    const input = document.getElementById('modalReasonInput');
    if (selectEl.value === '__custom__') {
        input.value = '';
        input.focus();
    } else {
        input.value = selectEl.value;
    }
}

function onSidebarProductChange(sel) {
    const opt = sel.options[sel.selectedIndex];
    const stock = opt.getAttribute('data-stock') || '0';
    const notice = document.getElementById('sidebarStockNotice');
    if (notice) {
        notice.innerHTML = 'Current stock: <strong>' + stock + ' units</strong>';
    }
}
</script>

<?php require __DIR__ . '/../layout-footer.php'; ?>

