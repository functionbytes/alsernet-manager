@extends('layouts.administratives')

@section('content')

    @include('managers.includes.card', ['title' => 'Importar órdenes del ERP'])

    <div class="widget-content">
        <div class="card card-body">
            <div class="row">
                <div class="col-md-12">
                    <h5 class="mb-4">Selecciona las órdenes del ERP que deseas importar</h5>

                    <div class="form-group mb-3">
                        <label for="order_ids_input" class="form-label">IDs de órdenes del ERP</label>
                        <div class="input-group">
                            <input type="text" id="order_ids_input" class="form-control" placeholder="Ingresa los IDs separados por comas. Ej: ORD001,ORD002,ORD003">
                            <button type="button" class="btn btn-primary" id="add-order-btn"><i class="fa-duotone fa-plus"></i></button>
                        </div>
                        <small class="text-muted">Escribe los IDs de las órdenes del ERP que deseas importar (separados por comas) y haz clic en "Agregar" o presiona Enter</small>
                    </div>

                    <div class="form-group mb-3" id="orders_list_container" style="display: none;">
                        <label class="form-label">Órdenes a importar</label>
                        <div id="orders_list" class="card p-3" style="min-height: 60px; background-color: #f8f9fa;">
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <button type="button" class="btn btn-primary  mb-2 w-100" id="import-btn" disabled>
                                Importar del ERP
                            </button>
                            <a href="{{ route('administrative.documents') }}" class="btn btn-secondary  w-100">
                                Volver
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resultado de importación -->
    <div id="results-container" style="display: none; margin-top: 30px;">
        <div class="card card-body">
            <h5 class="mb-4">Resultados de importación</h5>
            <div id="results-content"></div>
            <div class="mt-4">
                <a href="{{ route('administrative.documents') }}" class="btn btn-primary w-100">
                    Ver documentos
                </a>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación de importación -->
    <div id="import-confirm-modal" class="modal fade">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Importación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="display-4 text-primary"><i data-feather="download-cloud"></i></div>
                    <h4 class="my-3">¿Deseas importar estas órdenes del ERP?</h4>
                    <p id="import-count-text" class="text-muted"></p>
                    <div class="row justify-content-center mt-4">
                        <div class="col-sm-12 col-md-5">
                            <button type="button" id="confirm-import-btn" class="btn btn-primary w-100">Confirmar</button>
                        </div>
                        <div class="col-sm-12 col-md-5">
                            <button type="button" class="btn btn-light-danger w-100" data-bs-dismiss="modal">Cancelar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        console.log('Script de import-erp.blade.php cargado');

        (function() {
            console.log('IIFE ejecutándose');

            // Función para inicializar cuando el DOM esté listo
            function initializeImportForm() {
                console.log('initializeImportForm llamada');
                const orderIdsInput = document.getElementById('order_ids_input');
                const addOrderBtn = document.getElementById('add-order-btn');
                const ordersListContainer = document.getElementById('orders_list_container');
                const ordersList = document.getElementById('orders_list');
                const importBtn = document.getElementById('import-btn');
                const resultsContainer = document.getElementById('results-container');
                const resultsContent = document.getElementById('results-content');
                let selectedOrderIds = [];

                // Validar que los elementos existan
                if (!orderIdsInput || !addOrderBtn || !importBtn) {
                    console.error('No se encontraron los elementos del formulario de importación');
                    return;
                }
                console.log('Script de importación ERP inicializado correctamente');

                // Manejar clic en botón Agregar
                addOrderBtn.addEventListener('click', function() {
                    addOrderIds();
                });

                // Manejar entrada de IDs con Enter
                orderIdsInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        addOrderIds();
                    }
                });

                function addOrderIds() {
                    console.log('addOrderIds ejecutada');
                    const inputValue = orderIdsInput.value.trim();
                    console.log('inputValue:', inputValue);

                    if (!inputValue) {
                        console.log('Input vacío');
                        return;
                    }

                    // Dividir por comas y procesar cada ID
                    const ids = inputValue.split(',').map(id => {
                        return id.trim();
                    }).filter(id => id);

                    console.log('IDs procesados:', ids);

                    if (ids.length === 0) {
                        alert('Por favor ingresa IDs válidos separados por comas');
                        return;
                    }

                    // Agregar IDs que no estén duplicados
                    ids.forEach(id => {
                        if (!selectedOrderIds.includes(id)) {
                            selectedOrderIds.push(id);
                        }
                    });

                    console.log('selectedOrderIds actualizado:', selectedOrderIds);
                    orderIdsInput.value = ''; // Limpiar input
                    updateOrdersList();
                }

                function updateOrdersList() {
                    if (selectedOrderIds.length === 0) {
                        ordersListContainer.style.display = 'none';
                        importBtn.disabled = true;
                        return;
                    }

                    ordersListContainer.style.display = 'block';
                    ordersList.innerHTML = selectedOrderIds.map(id => `
                        <div class="badge bg-primary me-2 " >
                           ${id}
                            <button type="button" class="btn-close btn-close-white ms-2" style="font-size: 0.7rem;" onclick="removeOrderId('${id}')"></button>
                        </div>
                    `).join('');

                    importBtn.disabled = false;
                }

                window.removeOrderId = function(id) {
                    selectedOrderIds = selectedOrderIds.filter(oid => oid !== id);
                    updateOrdersList();
                };

                // Importar órdenes
                importBtn.addEventListener('click', function() {
                    if (selectedOrderIds.length === 0) {
                        alert('Por favor ingresa al menos una orden para importar');
                        return;
                    }

                    // Mostrar modal de confirmación
                    const importCountText = document.getElementById('import-count-text');
                    importCountText.textContent = `Se importarán ${selectedOrderIds.length} orden(es) del ERP`;
                    
                    const modal = new bootstrap.Modal(document.getElementById('import-confirm-modal'));
                    modal.show();
                });

                // Confirmar importación desde el modal
                document.getElementById('confirm-import-btn').addEventListener('click', function() {
                    // Cerrar modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('import-confirm-modal'));
                    modal.hide();

                    importBtn.disabled = true;
                    importBtn.innerHTML = '<i class="fa-duotone fa-spinner fa-spin"></i> Importando...';

                    importOrders(selectedOrderIds);
                });

                function importOrders(orderIds) {
                    const totalOrders = orderIds.length;
                    let importedCount = 0;
                    let resultsHtml = `
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Orden</th>
                                        <th>Detalles</th>
                                        <th class="text-end">Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;

                    const importNext = (index) => {
                        if (index >= orderIds.length) {
                            // Mostrar resultados
                            resultsHtml += '</tbody></table></div>';
                            showResults(resultsHtml, importedCount, totalOrders);
                            importBtn.disabled = false;
                            importBtn.innerHTML = 'Importar del ERP';
                            return;
                        }

                        const orderId = orderIds[index];

                        fetch(`/administrative/documents/sync/from-erp?order_id=${orderId}`, {
                            method: 'GET',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                                'Content-Type': 'application/json',
                            },
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.status === 'success') {
                                    importedCount++;
                                    const productsCount = data.data.products_count || 0;

                                    resultsHtml += `
                                        <tr>
                                            <td><strong>${orderId}</strong></td>
                                            <td>${productsCount} productos • ${data.data.customer_name || 'N/A'}</td>
                                            <td class="text-end"><span class="badge bg-success">Importada</span></td>
                                        </tr>
                                    `;
                                } else {
                                    const errorMessage = data.message || 'Error desconocido';

                                    resultsHtml += `
                                        <tr>
                                            <td><strong>${orderId}</strong></td>
                                            <td class="text-muted">${errorMessage}</td>
                                            <td class="text-end"><span class="badge bg-danger">Error</span></td>
                                        </tr>
                                    `;
                                }

                                importNext(index + 1);
                            })
                            .catch(error => {
                                console.error('Error:', error);

                                resultsHtml += `
                                    <tr>
                                        <td><strong>${orderId}</strong></td>
                                        <td class="text-muted">No se pudo procesar la solicitud</td>
                                        <td class="text-end"><span class="badge bg-danger">Error</span></td>
                                    </tr>
                                `;

                                importNext(index + 1);
                            });
                    };

                    importNext(0);
                }

                function showResults(html, imported, total) {
                    const failed = total - imported;
                    resultsContent.innerHTML = `
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="row text-center g-3">
                            <div class="col-md-4">
                                <div class="card p-3">
                                    <h2 class="mb-1 text-dark fw-bold">${total}</h2>
                                    <p class="mb-0 text-muted small">Total</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3">
                                    <h2 class="mb-1 text-success fw-bold">${imported}</h2>
                                    <p class="mb-0 text-muted small">Importadas</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3">
                                    <h2 class="mb-1 text-danger fw-bold">${failed}</h2>
                                    <p class="mb-0 text-muted small">Fallidas</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <h5 class="mb-3">Detalle de Importación</h5>
                ${html}
            `;
                    resultsContainer.style.display = 'block';
                    resultsContainer.scrollIntoView({ behavior: 'smooth' });
                }
            }

            // Ejecutar cuando el DOM esté listo (múltiples estrategias de compatibilidad)
            console.log('Verificando readyState:', document.readyState);
            if (document.readyState === 'loading') {
                console.log('Esperando DOMContentLoaded');
                document.addEventListener('DOMContentLoaded', initializeImportForm);
            } else {
                console.log('DOM ya está listo, ejecutando inmediatamente');
                initializeImportForm();
            }
        })();

        console.log('Script finalizado');
    </script>
@endpush
