<style>
.ai-fab {
    position: fixed;
    bottom: 22px;
    left: 22px;
    z-index: 1050;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border: 0;
    border-radius: 999px;
    padding: 12px 18px;
    background: linear-gradient(135deg, #0f4c81, #1a73b8);
    color: #fff;
    font-weight: 700;
    box-shadow: 0 10px 28px rgba(15, 76, 129, .35);
    cursor: pointer;
}
.ai-fab:hover { color: #fff; filter: brightness(1.05); }
.ai-panel {
    position: fixed;
    bottom: 80px;
    left: 22px;
    width: min(420px, calc(100vw - 24px));
    height: min(620px, calc(100vh - 110px));
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 18px 50px rgba(15, 23, 42, .28);
    z-index: 1051;
    display: none;
    flex-direction: column;
    overflow: hidden;
    border: 1px solid #dbe3ee;
}
.ai-panel.is-open { display: flex; }
.ai-panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 16px;
    background: linear-gradient(135deg, #0f4c81, #1a73b8);
    color: #fff;
}
.ai-panel-header small { display: block; opacity: .85; font-size: 11px; margin-top: 2px; }
.ai-close {
    border: 0;
    background: transparent;
    color: #fff;
    font-size: 28px;
    line-height: 1;
    cursor: pointer;
}
.ai-panel-body {
    flex: 1;
    overflow-y: auto;
    padding: 14px;
    background: #f8fafc;
}
.ai-msg { margin-bottom: 12px; display: flex; }
.ai-msg.ai-user { justify-content: flex-start; }
.ai-msg.ai-bot { justify-content: flex-end; }
.ai-bubble {
    max-width: 92%;
    padding: 10px 12px;
    border-radius: 14px;
    white-space: pre-wrap;
    line-height: 1.7;
    font-size: 13px;
}
.ai-bot .ai-bubble {
    background: #fff;
    border: 1px solid #e2e8f0;
    color: #0f172a;
    border-bottom-left-radius: 4px;
}
.ai-user .ai-bubble {
    background: #1a73b8;
    color: #fff;
    border-bottom-right-radius: 4px;
}
.ai-products {
    display: grid;
    gap: 8px;
    margin-top: 10px;
}
.ai-product-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 10px;
}
.ai-product-card strong { display: block; color: #0f4c81; margin-bottom: 4px; }
.ai-product-meta { font-size: 12px; color: #64748b; margin-bottom: 8px; }
.ai-product-actions { display: flex; flex-wrap: wrap; gap: 6px; }
.ai-product-actions .btn { font-size: 12px; padding: 4px 8px; }
.ai-links { display: grid; gap: 6px; margin-top: 10px; }
.ai-links a {
    display: block;
    background: #e8f4fc;
    color: #0f4c81;
    border-radius: 8px;
    padding: 8px 10px;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
}
.ai-links a:hover { background: #d9eef9; }
.ai-suggestions {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    padding: 8px 12px;
    border-top: 1px solid #eef2f7;
    background: #fff;
    max-height: 88px;
    overflow-y: auto;
}
.ai-chip {
    border: 1px solid #cfe3f5;
    background: #f0f7fc;
    color: #0f4c81;
    border-radius: 999px;
    padding: 5px 10px;
    font-size: 12px;
    cursor: pointer;
}
.ai-chip:hover { background: #d9eef9; }
.ai-panel-footer {
    display: flex;
    gap: 8px;
    padding: 10px;
    border-top: 1px solid #eef2f7;
    background: #fff;
}
.ai-panel-footer .form-control { border-radius: 10px; }
.ai-panel-footer .btn { border-radius: 10px; min-width: 44px; }
.ai-calc-box {
    margin-top: 10px;
    padding: 10px;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 10px;
}
.ai-calc-box .row { margin-left: -5px; margin-right: -5px; }
.ai-calc-box [class*="col-"] { padding-left: 5px; padding-right: 5px; }
.ai-typing { opacity: .7; font-style: italic; }
@media (max-width: 767px) {
    .ai-fab { left: 12px; bottom: 12px; }
    .ai-panel { left: 8px; right: 8px; width: auto; bottom: 70px; }
}
</style>
