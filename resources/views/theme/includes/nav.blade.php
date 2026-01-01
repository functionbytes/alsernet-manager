<!-- Sidebar Start -->

@php
    $navigationService = app(\App\Services\NavigationService::class);
    $navSections = $navigationService->getNavigation();
    $sectionIndex = 0;
@endphp

<aside class="side-mini-panel with-vertical">
    <!-- Vertical Layout Sidebar -->
    <div class="iconbar">
        <div>
            <div class="mini-nav">
                <div class="brand-logo d-flex align-items-center justify-content-center">
                    <a class="nav-link sidebartoggler" id="headerCollapse" href="javascript:void(0)">
                        <i class="fa fa-ellipsis"></i>
                    </a>
                </div>
                <ul class="mini-nav-ul simplebar-scrollable-y" data-simplebar="init">
                    @foreach($navSections as $sectionId => $section)
                        @php $sectionIndex++; @endphp
                        <li class="mini-nav-item" id="mini-{{ $sectionId }}">
                            <a href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-placement="right" data-bs-title="{{ $section['title'] }}">
                                <i class="fa {{ $section['icon'] }}"></i>
                            </a>
                        </li>

                        @if($sectionId === 'configuracion')
                            <li><span class="sidebar-divider lg"></span></li>
                        @endif
                    @endforeach
                </ul>
            </div>

            <div class="sidebarmenu">
                @php $navIndex = 0; @endphp
                @foreach($navSections as $sectionId => $section)
                    @php $navIndex++; @endphp
                    <nav class="sidebar-nav scroll-sidebar {{ $navIndex === 1 ? '' : 'd-none' }}" id="menu-right-{{ $sectionId }}" data-simplebar="init">
                        <ul class="sidebar-menu" id="sidebarnav-{{ $sectionId }}">
                            <li class="nav-small-cap">
                                <span class="hide-menu">{{ $section['title'] }}</span>
                            </li>

                            @forelse($section['items'] as $item)
                                <li class="sidebar-item">
                                    <a href="{{ $item['url'] }}" class="sidebar-link {{ $item['active'] ? 'active' : '' }}">
                                        <span class="hide-menu">{{ $item['label'] }}</span>
                                    </a>
                                </li>
                            @empty
                                <li class="sidebar-item">
                                    <span class="hide-menu text-muted">Sin opciones</span>
                                </li>
                            @endforelse
                        </ul>
                    </nav>
                @endforeach
            </div>
        </div>
    </div>
</aside>

<!-- Sidebar End -->
