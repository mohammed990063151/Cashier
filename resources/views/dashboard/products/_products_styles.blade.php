<style>
.products-toolbar {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 12px;
    margin-bottom: 15px;
}
.products-table thead th {
    background: #3c8dbc;
    color: #fff;
    text-align: center;
    vertical-align: middle !important;
    white-space: nowrap;
}
.products-table tbody td {
    vertical-align: middle !important;
    text-align: center;
}
.products-table .col-name {
    text-align: right !important;
    font-weight: 600;
    color: #1e3c72;
}
.products-table .product-thumb {
    width: 56px;
    height: 56px;
    object-fit: cover;
    border-radius: 6px;
}
.products-table .money {
    direction: ltr;
    display: inline-block;
    font-weight: 600;
}
.products-actions {
    display: inline-flex;
    flex-wrap: wrap;
    gap: 4px;
    justify-content: center;
}
.product-form-section {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
}
.product-form-section h4 {
    margin: 0 0 12px;
    font-size: 15px;
    font-weight: 700;
    color: #3c8dbc;
    border-bottom: 2px solid #e2e8f0;
    padding-bottom: 8px;
}
.sale-mode-help {
    background: #fff;
    border: 1px dashed #90caf9;
    border-radius: 8px;
    padding: 12px;
    margin-top: 10px;
    font-size: 13px;
    color: #475569;
}
.unit-switch {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
}
.unit-switch-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    padding: 12px 8px;
    border: 2px solid #dbe3ee;
    border-radius: 10px;
    background: #fff;
    color: #334155;
    cursor: pointer;
    transition: all .18s ease;
}
.unit-switch-btn i { font-size: 18px; color: #64748b; }
.unit-switch-btn strong { font-size: 14px; }
.unit-switch-btn small { color: #94a3b8; font-size: 11px; }
.unit-switch-btn:hover { border-color: #3c8dbc; background: #f0f7fc; }
.unit-switch-btn.is-active {
    border-color: #3c8dbc;
    background: linear-gradient(180deg, #e8f4fc 0%, #d9eef9 100%);
    box-shadow: 0 0 0 3px rgba(60,141,188,.12);
}
.unit-switch-btn.is-active i,
.unit-switch-btn.is-active strong { color: #1e5f8a; }
.unit-live-preview {
    margin-top: 8px;
    padding: 10px 12px;
    border-radius: 8px;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    color: #166534;
    font-size: 13px;
}
@media (max-width: 767px) {
    .unit-switch { grid-template-columns: 1fr; }
    .products-toolbar { padding: 10px; }
    .products-table td.col-name {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 8px !important;
    }
    .products-table td.col-name:before {
        flex: none !important;
        max-width: none !important;
        margin-bottom: 2px;
    }
    .products-table .col-name {
        text-align: right !important;
        font-size: 16px;
    }
    .products-table .product-thumb {
        width: 64px;
        height: 64px;
        flex-shrink: 0;
    }
    .products-actions {
        width: 100%;
    }
    .products-actions .btn {
        flex: 1 1 30%;
    }
    .product-form-section {
        padding: 12px;
        border-radius: 12px;
    }
    .product-image-actions .btn {
        min-height: 46px;
    }
}
.product-show-card .info-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px dashed #eee;
}
.product-show-card .info-row:last-child { border-bottom: none; }
.product-image-box {
    text-align: center;
}
.product-image-box .image-preview {
    width: 100%;
    max-height: 220px;
    object-fit: cover;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
}
.product-image-actions {
    margin-top: 12px;
    display: grid;
    gap: 8px;
}
.product-image-actions .btn {
    border-radius: 8px;
    font-weight: 600;
}
.product-image-hint {
    margin: 10px 0 0;
    font-size: 12px;
    color: #64748b;
    line-height: 1.6;
}
.product-image-filename {
    margin-top: 6px;
    font-size: 12px;
    word-break: break-all;
}
</style>
