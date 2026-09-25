<style>
.money-dual {
    display: inline-flex;
    flex-wrap: wrap;
    align-items: baseline;
    gap: 6px;
    line-height: 1.3;
}
.money-dual-block { display: flex; flex-direction: column; gap: 2px; }
.money-sdg { font-weight: 700; color: #0f172a; }
.money-sdg small { font-weight: 600; color: #64748b; }
.money-usd {
    font-size: 12px;
    font-weight: 700;
    color: #0f766e;
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
    border-radius: 999px;
    padding: 1px 8px;
    direction: ltr;
}
.fx-rate-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(255,255,255,.16);
    color: #fff !important;
    border-radius: 999px;
    padding: 6px 10px;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none !important;
    margin: 0 4px;
    white-space: nowrap;
}
.fx-rate-chip:hover { background: rgba(255,255,255,.24); color: #fff !important; }
.fx-calc-fab {
    position: fixed;
    left: 14px;
    bottom: 78px;
    z-index: 1035;
    border: 0;
    border-radius: 999px;
    background: #0f766e;
    color: #fff;
    min-height: 46px;
    padding: 0 14px;
    font-weight: 700;
    box-shadow: 0 6px 18px rgba(15, 118, 110, .35);
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.fx-calc-panel {
    display: none;
    position: fixed;
    left: 14px;
    bottom: 132px;
    width: min(320px, calc(100vw - 28px));
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 12px 36px rgba(15, 23, 42, .22);
    z-index: 1036;
    overflow: hidden;
    border: 1px solid #e2e8f0;
}
.fx-calc-panel.is-open { display: block; }
.fx-calc-panel-head {
    background: linear-gradient(135deg, #0f766e, #0d9488);
    color: #fff;
    padding: 12px 14px;
    font-weight: 800;
}
.fx-calc-panel-body { padding: 12px 14px 14px; }
.fx-calc-panel .form-control { min-height: 42px; font-size: 16px; margin-bottom: 8px; }
.fx-calc-panel .btn { min-height: 42px; width: 100%; margin-bottom: 8px; }
.fx-calc-out {
    background: #f0fdfa;
    border: 1px solid #99f6e4;
    border-radius: 10px;
    padding: 10px;
    font-weight: 700;
    color: #115e59;
    min-height: 42px;
}
.product-usd-hint {
    margin-top: 6px;
    font-size: 12px;
    color: #0f766e;
    font-weight: 700;
}
@media (max-width: 767px) {
    .fx-calc-fab { bottom: 78px; left: 12px; }
    .fx-calc-panel { bottom: 130px; left: 12px; }
    .main-header.phone-header .fx-rate-chip {
        max-width: 42vw;
        overflow: hidden;
        text-overflow: ellipsis;
    }
}
</style>
