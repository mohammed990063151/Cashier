<style>
/* ── شريط البحث والفلاتر ── */
.orders-toolbar {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 12px;
    margin-bottom: 0;
}
.orders-toolbar .form-control {
    height: 36px;
}
.orders-toolbar .payment-status-filter {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
}
.orders-toolbar .payment-status-filter .btn {
    border-radius: 4px !important;
    margin: 0 !important;
}

/* ── جدول الطلبات ── */
.orders-table-wrap {
    margin: 0;
}
.orders-table {
    margin-bottom: 0;
    font-size: 13px;
}
.orders-table thead th {
    background: #3c8dbc;
    color: #fff;
    text-align: center;
    white-space: nowrap;
    vertical-align: middle !important;
    border-color: #367fa9 !important;
    font-weight: 600;
    padding: 10px 8px !important;
}
.orders-table tbody td {
    vertical-align: middle !important;
    text-align: center;
    padding: 10px 8px !important;
}
.orders-table tbody tr.orders-row:hover {
    background: #f0f7ff;
}
.orders-table tbody tr.orders-row.active {
    background: #e3f2fd;
    box-shadow: inset 3px 0 0 #3c8dbc;
}
.orders-table .col-order-no {
    font-weight: 700;
    color: #1e3c72;
    white-space: nowrap;
}
.orders-table .col-client {
    text-align: right !important;
    max-width: 120px;
}
.orders-table .money {
    font-weight: 600;
    white-space: nowrap;
    direction: ltr;
    display: inline-block;
}
.orders-table .money-total { color: #2e7d32; }
.orders-table .money-discount { color: #b8860b; }
.orders-table .money-after { color: #1565c0; }
.orders-table .money-paid { color: #1b5e20; }
.orders-table .money-remain-zero { color: #2e7d32; }
.orders-table .money-remain-due { color: #c62828; }
.orders-table .col-date {
    font-size: 12px;
    color: #666;
    white-space: nowrap;
}
.orders-table .col-actions {
    white-space: nowrap;
    min-width: 200px;
}
.orders-actions {
    display: inline-flex;
    flex-wrap: wrap;
    gap: 4px;
    justify-content: center;
}
.orders-actions .btn {
    padding: 4px 8px;
    font-size: 12px;
}

/* ── لوحة المعاينة ── */
.orders-preview-box .box-header {
    background: linear-gradient(135deg, #3c8dbc 0%, #367fa9 100%);
    color: #fff;
    padding: 12px 15px;
    border-radius: 3px 3px 0 0;
}
.orders-preview-box .box-header .box-title {
    color: #fff;
    font-size: 15px;
    margin: 0;
}
.orders-preview-box .box-body {
    padding: 0;
    min-height: 320px;
}
.orders-preview-body {
    padding: 15px;
    max-height: calc(100vh - 220px);
    overflow-y: auto;
}
.orders-preview-empty {
    text-align: center;
    padding: 40px 20px;
    color: #94a3b8;
}
.orders-preview-empty i {
    font-size: 42px;
    margin-bottom: 12px;
    color: #cbd5e1;
}
.orders-preview-empty p {
    margin: 0;
    font-size: 14px;
    line-height: 1.6;
}
#loading.orders-loading {
    display: none;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
}
#loading.orders-loading .loader {
    border: 3px solid #e2e8f0;
    border-top-color: #3c8dbc;
    border-radius: 50%;
    width: 36px;
    height: 36px;
    animation: orders-spin 0.8s linear infinite;
}
@keyframes orders-spin {
    to { transform: rotate(360deg); }
}

@media (max-width: 991px) {
    .orders-preview-box {
        margin-top: 15px;
    }
    .orders-table .col-actions {
        min-width: auto;
    }
}
@media (max-width: 767px) {
    .orders-toolbar {
        padding: 10px;
        border-radius: 12px;
    }
    .orders-toolbar .form-control {
        height: 46px !important;
        font-size: 16px !important;
    }
    .orders-toolbar .payment-status-filter {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }
    .orders-toolbar .payment-status-filter .btn {
        width: 100%;
        min-height: 42px;
        margin: 0 !important;
    }
    .orders-table {
        font-size: 14px;
    }
    .orders-actions {
        width: 100%;
    }
    .orders-actions .btn {
        flex: 1 1 45%;
        min-height: 42px;
        font-size: 13px !important;
    }
    .orders-preview-body {
        max-height: none;
        padding: 12px;
    }
    .orders-preview-empty {
        padding: 28px 14px;
    }
}
</style>
