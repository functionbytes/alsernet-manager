@extends('layouts.theme')

@section('content')

    @include('theme.components.card', ['title' => 'Matriz de permisos por rol'])

    {{-- Alertas --}}
    @include('theme.components.alerts')

    <div class="card">
        <div class="card-header bg-white border-bottom">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-1">Matriz de permisos</h5>
                    <p class="text-muted mb-0 small">Vista completa de todos los permisos asignados a cada rol del sistema</p>
                </div>
                <div class="col-auto">
                    <a href="{{ route('settings.roles.index') }}" class="btn btn-light">
                        <i class="fas fa-arrow-left me-2"></i>Atrás
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            {{-- Info --}}
            <div class="alert alert-info border-info m-3">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Información:</strong> Esta matriz muestra todos los permisos disponibles en el sistema y su asignación a cada rol.
                Haz clic en las celdas para asignar o revocar permisos.
            </div>

            {{-- Responsive table wrapper --}}
            <div class="table-responsive">
                <table class="table table-hover mb-0 permission-matrix">
                    <thead>
                        <tr class="bg-light">
                            <th class="sticky-col" style="min-width: 180px;">
                                <strong>Roles</strong>
                            </th>
                            @foreach ($permissionsByModule as $module => $categories)
                                @foreach ($categories as $category => $perms)
                                    <th class="text-center" style="min-width: 120px; background-color: #f8f9fa;">
                                        <small class="d-block text-uppercase fw-bold">{{ $module }}</small>
                                        <small class="d-block text-muted">{{ $category }}</small>
                                    </th>
                                @endforeach
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($roles as $role)
                            <tr>
                                <td class="sticky-col fw-bold" style="min-width: 180px;">
                                    <div class="d-flex flex-column">
                                        <span>{{ $role->name }}</span>
                                        <small class="text-muted">
                                            <span class="badge bg-info permission-count-{{ $role->id }}">
                                                {{ count($rolePermissions[$role->id]) }} permisos
                                            </span>
                                        </small>
                                    </div>
                                </td>

                                @foreach ($permissionsByModule as $module => $categories)
                                    @foreach ($categories as $category => $perms)
                                        <td class="text-center">
                                            @foreach ($perms as $permission)
                                                <div class="form-check form-check-inline">
                                                    <input
                                                        class="form-check-input permission-checkbox"
                                                        type="checkbox"
                                                        id="perm_{{ $role->id }}_{{ $permission->id }}"
                                                        data-role-id="{{ $role->id }}"
                                                        data-permission-id="{{ $permission->id }}"
                                                        data-role-name="{{ $role->name }}"
                                                        data-permission-name="{{ $permission->name }}"
                                                        {{ in_array($permission->id, $rolePermissions[$role->id]) ? 'checked' : '' }}>
                                                    <label
                                                        class="form-check-label d-none"
                                                        for="perm_{{ $role->id }}_{{ $permission->id }}">
                                                    </label>
                                                </div>
                                            @endforeach
                                        </td>
                                    @endforeach
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer bg-white border-top">
            <div class="row">
                <div class="col-md-6">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="exportMatrix">
                        <i class="fas fa-download me-2"></i>Exportar matriz
                    </button>
                </div>
                <div class="col-md-6 text-end">
                    <span class="text-muted small">
                        Total: <strong>{{ count($permissions) }}</strong> permisos | <strong>{{ count($roles) }}</strong> roles
                    </span>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
<style>
    .permission-matrix {
        font-size: 0.85rem;
    }

    .sticky-col {
        position: sticky;
        left: 0;
        background-color: white;
        z-index: 10;
        box-shadow: 2px 0 3px rgba(0, 0, 0, 0.1);
    }

    thead .sticky-col {
        background-color: #f8f9fa !important;
        font-weight: bold;
    }

    .permission-matrix th {
        padding: 0.75rem !important;
        font-size: 0.8rem;
    }

    .permission-matrix td {
        padding: 0.5rem !important;
        vertical-align: middle;
    }

    .form-check-input {
        width: 1.1rem;
        height: 1.1rem;
        cursor: pointer;
    }

    .form-check-input:checked {
        background-color: #90bb13;
        border-color: #90bb13;
    }

    .form-check-input:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .permission-matrix tbody tr:hover {
        background-color: rgba(144, 187, 19, 0.05);
    }

    .badge {
        font-size: 0.7rem;
        padding: 0.35rem 0.6rem;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Handle permission checkbox changes
        $('.permission-checkbox').on('change', function() {
            const roleId = $(this).data('role-id');
            const permissionId = $(this).data('permission-id');
            const roleName = $(this).data('role-name');
            const permissionName = $(this).data('permission-name');
            const isChecked = $(this).is(':checked');

            // Disable checkbox while processing
            const $checkbox = $(this);
            $checkbox.prop('disabled', true);

            // Send AJAX request
            $.ajax({
                url: "{{ route('settings.roles.update.permissions', '') }}/" + roleId,
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    permission_id: permissionId,
                    action: isChecked ? 'attach' : 'detach'
                },
                success: function(response) {
                    $checkbox.prop('disabled', false);

                    // Update permission count
                    updateRolePermissionCount(roleId);

                    const message = isChecked
                        ? `Permiso "${permissionName}" asignado a "${roleName}"`
                        : `Permiso "${permissionName}" revocado de "${roleName}"`;

                    toastr.success(message, 'Éxito', { positionClass: 'toast-bottom-right' });
                },
                error: function(xhr) {
                    $checkbox.prop('disabled', false);
                    // Revert checkbox state
                    $checkbox.prop('checked', !isChecked);

                    const errorMessage = xhr.responseJSON?.message || 'Error al actualizar permiso. Intente nuevamente.';
                    toastr.error(errorMessage, 'Error', { positionClass: 'toast-bottom-right' });
                }
            });
        });

        // Update permission count for a role
        function updateRolePermissionCount(roleId) {
            const count = $(`input[data-role-id="${roleId}"]:checked`).length;
            $(`.permission-count-${roleId}`).text(count + ' permisos');
        }

        // Export matrix as CSV
        $('#exportMatrix').on('click', function() {
            const csv = [
                ['Role', ...@json($permissions->pluck('name'))]
            ];

            @foreach ($roles as $role)
                const row = ['{{ $role->name }}'];
                @foreach ($permissions as $permission)
                    row.push(
                        $(`#perm_{{ $role->id }}_{{ $permission->id }}`).is(':checked') ? '✓' : ''
                    );
                @endforeach
                csv.push(row);
            @endforeach

            // Convert to CSV string
            const csvContent = csv.map(row => row.map(cell => `"${cell}"`).join(',')).join('\n');

            // Create download link
            const link = document.createElement('a');
            link.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csvContent);
            link.download = `permission-matrix-${new Date().toISOString().split('T')[0]}.csv`;
            link.click();

            toastr.success('Matriz exportada correctamente', 'Éxito', { positionClass: 'toast-bottom-right' });
        });
    });
</script>
@endpush
