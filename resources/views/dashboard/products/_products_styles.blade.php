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

/* بطاقات المنتجات للهاتف */
.products-phone-list { display: none; }
.phone-product-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    margin-bottom: 14px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(15, 23, 42, .06);
}
.phone-product-head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}
.phone-product-img {
    width: 64px;
    height: 64px;
    object-fit: cover;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    flex-shrink: 0;
    background: #fff;
}
.phone-product-title { min-width: 0; flex: 1; }
.phone-product-title strong {
    display: block;
    font-size: 17px;
    color: #0f172a;
    line-height: 1.35;
    margin-bottom: 4px;
}
.phone-product-title span {
    display: block;
    font-size: 13px;
    color: #64748b;
}
.phone-product-meta { padding: 8px 14px; }
.phone-meta-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    padding: 10px 0;
    border-bottom: 1px solid #f1f5f9;
    font-size: 14px;
}
.phone-meta-row:last-child { border-bottom: none; }
.phone-meta-row > span:first-child {
    color: #64748b;
    font-weight: 700;
    flex: 0 0 34%;
}
.phone-meta-row > strong,
.phone-meta-row > span:last-child {
    text-align: left;
    flex: 1;
    min-width: 0;
}
.phone-action-bar {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
    padding: 12px 14px 14px;
    background: #fafbfc;
    border-top: 1px solid #e2e8f0;
}
.phone-action-bar .delete-form {
    grid-column: 1 / -1;
    margin: 0;
}
.phone-action-bar .btn {
    min-height: 48px !important;
    font-size: 15px !important;
    font-weight: 700 !important;
    border-radius: 10px !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    margin: 0 !important;
    width: 100%;
}
.products-pagination {
    margin-top: 16px;
    padding-top: 8px;
}
.products-pagination .pagination {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 6px;
}
.products-pagination .pagination > li > a,
.products-pagination .pagination > li > span {
    min-width: 42px;
    min-height: 42px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px !important;
    font-size: 14px;
    margin: 0 !important;
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
.product-show-card .info-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px dashed #eee;
}
.product-show-card .info-row:last-child { border-bottom: none; }
.product-image-box { text-align: center; }
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

@media (max-width: 767px) {
    .unit-switch { grid-template-columns: 1fr; }
    .products-toolbar { padding: 10px; }
    .products-phone-list { display: block !important; }
    .product-form-section { padding: 12px; border-radius: 12px; }
    .product-image-actions .btn { min-height: 46px; }
}
@media (min-width: 768px) {
    .products-phone-list { display: none !important; }
}
</style>
