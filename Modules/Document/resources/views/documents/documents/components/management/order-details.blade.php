{{-- Component: Order Details Card --}}
<div class="card mb-3">
    <div class="card-header p-3 bg-white border-bottom">
        <h5 class="mb-1 fw-bold">Detalle de la orden</h5>
        <p class="small mb-0 text-muted">Información de la orden y fechas</p>
    </div>
    <div class="card-body">
        <form id="formDocuments" enctype="multipart/form-data" role="form" onSubmit="return false">
            {{ csrf_field() }}
            <input type="hidden" id="uid" name="uid" value="{{ $document->uid }}">

            <div class="row g-3">
                <div class="col-sm-12 col-md-6">
                    <label class="form-label fw-semibold">Orden</label>
                    <input type="text" class="form-control" value="{{$document->order_id}}" disabled>
                </div>
                <div class="col-sm-12 col-md-6">
                    <label class="form-label fw-semibold">Referencia</label>
                    <input type="text" class="form-control" value="{{$document->order_reference}}" disabled>
                </div>
                <div class="col-sm-12 col-md-6">
                    <label class="form-label fw-semibold">Tipo</label>
                    <input type="text" class="form-control" value="{{$documentTypeLabel}}" disabled>
                </div>
                <div class="col-sm-12 col-md-6">
                    <label class="form-label fw-semibold">Fecha de orden</label>
                    <input type="text" class="form-control" value="{{ $document->order_date ? \Carbon\Carbon::parse($document->order_date)->format('d/m/Y H:i') : '' }}" disabled>
                </div>
                <div class="col-sm-12 col-md-6">
                    <label class="form-label fw-semibold">Fecha de confirmación</label>
                    <input type="text" class="form-control" value="{{ $document->confirmed_at ? \Carbon\Carbon::parse($document->confirmed_at)->format('d/m/Y H:i') : '' }}" disabled>
                </div>
                <div class="col-sm-12 col-md-6">
                    <label class="form-label fw-semibold">Fecha de creación</label>
                    <input type="text" class="form-control" value="{{ $document->created_at ? \Carbon\Carbon::parse($document->created_at)->format('d/m/Y H:i') : '' }}" disabled>
                </div>
            </div>
        </form>
    </div>
</div>
