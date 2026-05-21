<div>
    @if($show)
    <div class="modal fade show" style="display:block; z-index:1050;" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" wire:click="close">&times;</button>
                    <h4 class="modal-title" style="font-size:18px;">
                        <i class="fa fa-list"></i> سجل المدفوعات
                    </h4>
                </div>
                <div class="modal-body">
                    @if($order)
                        @include('dashboard.payments._payment_log_body', ['order' => $order, 'summary' => $summary])
                    @else
                        <p class="text-danger text-center">الطلب غير موجود</p>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-lg" wire:click="close">إغلاق</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show" style="z-index:1040;"></div>
    @endif
</div>
