<form method="post" action="{{ $action }}" style="display:inline-block;min-width:150px;vertical-align:top;" onsubmit="return confirm(@json($confirm));">
    @csrf
    <input type="text" name="note" class="form-control input-sm" maxlength="500" placeholder="سبب اختياري" style="margin-bottom:4px;min-height:36px;">
    <button type="submit" class="btn btn-default btn-sm btn-block" style="min-height:36px;">
        <i class="fa fa-ban"></i> {{ $label }}
    </button>
</form>
