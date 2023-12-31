<ul class="breadcrumb" itemscope itemtype="http://schema.org/BreadcrumbList">
    @foreach ($crumbs = Theme::breadcrumb()->getCrumbs() as $i => $crumb)
        @if ($i != (count($crumbs) - 1))
            <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem" class="breadcrumb-item">
                <a href="{{ $crumb['url'] }}" itemprop="item" title="{{ $crumb['label'] }}">
                    {{ $crumb['label'] }}
                    <meta itemprop="name" content="{{ $crumb['label'] }}" />
                </a>
                <meta itemprop="position" content="{{ $i + 1}}" />
            </li>
        @else
            <li class="breadcrumb-item active" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                {!! __($crumb['label']) !!}
                <meta itemprop="name" content="{{ $crumb['label'] }}" />
                <meta itemprop="position" content="{{ $i + 1}}" />
            </li>
        @endif
    @endforeach
</ul>
