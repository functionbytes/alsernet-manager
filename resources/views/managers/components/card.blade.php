{{-- Card component for manager pages with breadcrumbs --}}
<div class="card position-relative overflow-hidden">
    <div class="card-body px-4 py-3">
        <div class="row align-items-center">
            <div class="col-12">
                <h5 class="fw-semibold mb-3">
                    @if(isset($icon))
                        <i class="fas {{ $icon }} me-2"></i>
                    @endif
                    {{ $title }}
                </h5>

                @if(isset($breadcrumbs) && is_array($breadcrumbs))
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a class="text-muted text-decoration-none" href="{{ route('home') }}">
                                    <i class="fas fa-home me-1"></i>Dashboard
                                </a>
                            </li>
                            @foreach($breadcrumbs as $breadcrumb)
                                @if(isset($breadcrumb['url']))
                                    <li class="breadcrumb-item">
                                        <a class="text-muted text-decoration-none" href="{{ $breadcrumb['url'] }}">
                                            {{ $breadcrumb['label'] }}
                                        </a>
                                    </li>
                                @else
                                    <li class="breadcrumb-item active" aria-current="page">
                                        {{ $breadcrumb['label'] }}
                                    </li>
                                @endif
                            @endforeach
                        </ol>
                    </nav>
                @endif
            </div>
        </div>
    </div>
</div>
