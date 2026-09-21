<button type="button" id="ai-assistant-toggle" class="ai-fab" title="المساعد الذكي" aria-label="المساعد الذكي">
    <i class="fa fa-comments"></i>
    <span>مساعد AI</span>
</button>

<div id="ai-assistant-panel" class="ai-panel" aria-hidden="true">
    <div class="ai-panel-header">
        <div>
            <strong><i class="fa fa-magic"></i> المساعد الذكي</strong>
            <small>بيانات حقيقية من النظام</small>
        </div>
        <button type="button" class="ai-close" id="ai-assistant-close" aria-label="إغلاق">&times;</button>
    </div>

    <div class="ai-panel-body" id="ai-assistant-messages">
        <div class="ai-msg ai-bot">
            <div class="ai-bubble">جاري التحميل…</div>
        </div>
    </div>

    <div class="ai-suggestions" id="ai-assistant-suggestions"></div>

    <form class="ai-panel-footer" id="ai-assistant-form">
        <input type="text" id="ai-assistant-input" class="form-control" placeholder="اسأل عن مخزون، كراتين، تقرير، مبيعات…" autocomplete="off">
        <button type="submit" class="btn btn-primary" id="ai-assistant-send">
            <i class="fa fa-paper-plane"></i>
        </button>
    </form>
</div>
