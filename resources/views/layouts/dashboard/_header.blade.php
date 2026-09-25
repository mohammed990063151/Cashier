<header class="main-header phone-header">

    <a href="{{ route('dashboard.welcome') }}" class="logo">
        <span class="logo-mini">A<b>T</b>B</span>
        <span class="logo-lg"><b>{{ $setting->name ?? '' }}</b></span>
    </a>

    <nav class="navbar navbar-static-top">
        <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button" aria-label="القائمة">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </a>

        <a href="{{ route('dashboard.welcome') }}" class="phone-header-brand visible-xs">
            <span class="phone-header-brand-text">{{ $setting->name ?? 'لوحة التحكم' }}</span>
        </a>

        @php
            $fxHeader = app(\App\Services\CurrencyService::class);
        @endphp
        @if($fxHeader->enabled())
            <a href="{{ route('dashboard.exchange-rates.index') }}" class="fx-rate-chip" title="تحديث سعر الدولار">
                <i class="fa fa-dollar"></i> {{ $fxHeader->rateLabel() }}
            </a>
        @endif

        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
                @php $headerAlertsTotal = (int) ($collectionAlertsCount ?? 0) + (int) ($stockAlertsCount ?? 0); @endphp
                <li class="dropdown notifications-menu">
                    <a href="#" class="dropdown-toggle phone-header-icon" data-toggle="dropdown" title="التنبيهات">
                        <i class="fa fa-bell"></i>
                        @if($headerAlertsTotal > 0)
                            <span class="label label-warning phone-header-badge" id="header-alerts-badge">{{ $headerAlertsTotal }}</span>
                        @endif
                    </a>
                    <ul class="dropdown-menu phone-header-dropdown">
                        <li class="header">
                            تنبيهات المخزون
                            @if(($stockAlertsCount ?? 0) > 0)
                                ({{ $stockAlertsCount }})
                            @endif
                        </li>
                        <li>
                            <ul class="menu" id="stock-alerts-menu">
                                @forelse($stockAlerts ?? [] as $stockAlert)
                                    <li>
                                        <a href="{{ $stockAlert['url'] }}">
                                            <i class="fa fa-cube text-{{ ($stockAlert['level'] ?? '') === 'out' ? 'red' : 'yellow' }}"></i>
                                            {{ $stockAlert['name'] }}
                                            <br>
                                            <small>
                                                {{ $stockAlert['level_label'] }} —
                                                المتاح: {{ $stockAlert['stock_display'] }}
                                            </small>
                                        </a>
                                    </li>
                                @empty
                                    <li><a href="#"><small class="text-muted">لا توجد منتجات منخفضة المخزون</small></a></li>
                                @endforelse
                            </ul>
                        </li>
                        <li class="header" style="border-top:1px solid #f4f4f4;">
                            تنبيهات السداد
                            @if(($collectionAlertsCount ?? 0) > 0)
                                ({{ $collectionAlertsCount }})
                            @endif
                        </li>
                        <li>
                            <ul class="menu">
                                @forelse($collectionAlerts ?? [] as $alert)
                                    @php
                                        $alertSchedule = app(\App\Services\CollectionScheduleService::class);
                                        $alertStatus = $alert['status'] ?? 'due_soon';
                                        $alertOrder = $alert['order'];
                                        $alertInst = $alert['installment'];
                                    @endphp
                                    <li>
                                        <a href="{{ route('dashboard.payments.index', ['client_id' => $alertOrder->client_id, 'order_id' => $alertOrder->id]) }}">
                                            <i class="fa fa-warning text-{{ $alertStatus === 'overdue' ? 'red' : 'yellow' }}"></i>
                                            {{ $alertOrder->client->name }} — {{ $alertOrder->order_number }}
                                            <br>
                                            <small>
                                                قسط {{ number_format($alertInst->amount, 2) }} ج.س —
                                                {{ $alertSchedule->scheduleStatusLabel($alertStatus) }}
                                                ({{ $alertInst->due_at->format('d/m/Y') }})
                                            </small>
                                        </a>
                                    </li>
                                @empty
                                    <li><a href="#"><small class="text-muted">لا توجد تنبيهات سداد حالياً</small></a></li>
                                @endforelse
                            </ul>
                        </li>
                        <li class="footer">
                            <a href="{{ route('dashboard.products.index') }}">المنتجات</a>
                            ·
                            <a href="{{ route('dashboard.collection-schedules.index', ['schedule_status' => 'alert']) }}">السداد</a>
                        </li>
                    </ul>
                </li>

                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle phone-header-user" data-toggle="dropdown">
                        <span class="phone-header-avatar"><i class="fa fa-user"></i></span>
                        <span class="hidden-xs">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="user-header">
                            <span class="phone-header-avatar phone-header-avatar-lg"><i class="fa fa-user"></i></span>
                            <p>{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</p>
                        </li>
                        <li class="user-footer">
                            <a href="{{ route('logout') }}" class="btn btn-default btn-flat"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">تسجيل خروج</a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>

</header>
