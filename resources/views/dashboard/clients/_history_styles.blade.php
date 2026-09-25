<style>
.history-status-group {
    border-radius: 12px;
    overflow: hidden;
}
.history-group-head {
    background: #f8fafc;
}
.history-group-head .box-title {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.history-group-count {
    color: #64748b;
    font-size: 13px;
}
.history-group-table .money-total { color: #01941f; font-weight: 700; }
.history-group-table .money-paid { color: #2980b9; font-weight: 700; }
.history-group-table .money-remain { color: #e74c3c; font-weight: 700; }
.history-group-total {
    background: #f1f5f9 !important;
}
.history-group-total td {
    border-top: 2px solid #cbd5e1 !important;
    font-size: 14px;
}
.history-products-cell {
    background: #fafbfc;
    padding: 12px 16px !important;
    font-size: 13px;
    line-height: 1.7;
}
.history-row-actions {
    display: inline-flex;
    flex-wrap: wrap;
    gap: 6px;
    justify-content: center;
}
.history-grand-box {
    border-radius: 12px;
    border-top-color: #00a65a;
}
.history-grand-title {
    font-size: 16px;
    font-weight: 800;
    margin-bottom: 12px;
    color: #0f172a;
}
.history-grand-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
}
.history-grand-item {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 12px;
    text-align: center;
}
.history-grand-item span {
    display: block;
    color: #64748b;
    font-size: 12px;
    font-weight: 700;
    margin-bottom: 6px;
}
.history-grand-item strong {
    display: block;
    font-size: 18px;
    direction: ltr;
}
@media (max-width: 767px) {
    .history-grand-grid {
        grid-template-columns: 1fr 1fr;
    }
    .history-group-table tfoot tr.history-group-total {
        display: block;
        width: 100%;
        margin: 0;
        border: 0;
        border-radius: 0;
        box-shadow: none;
        background: #eef6ff !important;
        padding: 10px 12px;
    }
    .history-group-table tfoot tr.history-group-total > td {
        display: flex !important;
        justify-content: space-between;
        border: 0 !important;
        border-bottom: 1px dashed #dbe3ee !important;
        padding: 8px 4px !important;
        background: transparent !important;
    }
    .history-group-table tfoot tr.history-group-total > td:last-child {
        border-bottom: 0 !important;
    }
    .history-products-row td {
        display: block !important;
        width: 100% !important;
    }
    .history-row-actions {
        display: grid !important;
        grid-template-columns: 1fr 1fr;
        width: 100%;
    }
    .history-row-actions .btn {
        width: 100%;
        min-height: 44px;
    }
}
</style>
