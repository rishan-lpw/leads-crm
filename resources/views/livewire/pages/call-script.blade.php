@extends('layouts.app')

@section('content')
<div class="container-fluid" style="margin-top: 20px; margin-left: 200px; margin-right: 200px;">
    <div class="row">
        <div class="col-lg-10 col-lg-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <h2 class="panel-title" id="scriptModalLabel" style="font-weight: bold; font-size: 32px; color: #ffffff; margin: 0;">
                                Sales Call Transcript
                            </h2>
                        </div>
                        {{-- Take the mobile number from the customer table column 'mobile'. --}}
                        <button type="button" class="btn btn-success call-btn" data-phone="{{ request()->query('mobile') ?? request()->query('phone') ?? '' }}" style="background-color: #10b981; border-color: #10b981; color: white; padding: 10px 20px; border-radius: 6px; font-weight: 600; cursor: pointer; transition: all 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                            <i class="fa fa-phone"></i> Call Customer
                        </button>
                    </div>
                </div>

                <div class="panel-body">

                    <ul class="nav nav-tabs" id="tabs" style="border-bottom: 2px solid #e5e7eb; margin-top: 20px; margin-bottom: 0; display: flex; list-style: none; padding-left: 0;">
                        <li class="{{ ($activeTab ?? 'sinhala') === 'sinhala' ? 'active' : '' }}" style="margin-right: 4px;">
                            <a href="{{ route('call.script.sinhala', ['uid' => $uid]) }}" 
                               style="display: inline-block; padding: 12px 20px; font-size: 14px; font-weight: 500; 
                                      color: {{ ($activeTab ?? 'sinhala') === 'sinhala' ? '#2563eb' : '#6b7280' }}; 
                                      background-color: {{ ($activeTab ?? 'sinhala') === 'sinhala' ? 'white' : '#f9fafb' }}; 
                                      border: 2px solid {{ ($activeTab ?? 'sinhala') === 'sinhala' ? '#2563eb' : 'transparent' }}; 
                                      border-bottom: none; border-radius: 6px 6px 0 0; text-decoration: none; cursor: pointer; transition: all 0.2s;">
                                Sinhala
                            </a>
                        </li>
                        <li style="margin-right: 4px;">
                            <a href="#eng" style="display: inline-block; padding: 12px 20px; font-size: 14px; font-weight: 500; color: #6b7280; background-color: #f9fafb; border: 2px solid transparent; border-bottom: none; border-radius: 6px 6px 0 0; text-decoration: none; cursor: pointer; transition: all 0.2s;">
                                English
                            </a>
                        </li>
                        <li style="margin-right: 4px;">
                            <a href="#tam" style="display: inline-block; padding: 12px 20px; font-size: 14px; font-weight: 500; color: #6b7280; background-color: #f9fafb; border: 2px solid transparent; border-bottom: none; border-radius: 6px 6px 0 0; text-decoration: none; cursor: pointer; transition: all 0.2s;">
                                Tamil
                            </a>
                        </li>
                        <li id="bundle_packages_li" class="{{ ($activeTab ?? '') === 'bundle-package' ? 'active' : '' }}" style="margin-right: 4px;">
                            <a href="{{ route('call.script.bundle-package', ['uid' => $uid]) }}" 
                               style="display: inline-block; padding: 12px 20px; font-size: 14px; font-weight: 500; 
                                      color: {{ ($activeTab ?? '') === 'bundle-package' ? '#2563eb' : '#6b7280' }}; 
                                      background-color: {{ ($activeTab ?? '') === 'bundle-package' ? 'white' : '#f9fafb' }}; 
                                      border: 2px solid {{ ($activeTab ?? '') === 'bundle-package' ? '#2563eb' : 'transparent' }}; 
                                      border-bottom: none; border-radius: 6px 6px 0 0; text-decoration: none; cursor: pointer; transition: all 0.2s;">
                                Bundle Packages
                            </a>
                        </li>
                        <li id="stats_li" class="{{ ($activeTab ?? '') === 'stats' ? 'active' : '' }}" style="margin-right: 4px;">
                            <a href="{{ route('call.script.stats', ['uid' => $uid]) }}" 
                               style="display: inline-block; padding: 12px 20px; font-size: 14px; font-weight: 500; 
                                      color: {{ ($activeTab ?? '') === 'stats' ? '#2563eb' : '#6b7280' }}; 
                                      background-color: {{ ($activeTab ?? '') === 'stats' ? 'white' : '#f9fafb' }}; 
                                      border: 2px solid {{ ($activeTab ?? '') === 'stats' ? '#2563eb' : 'transparent' }}; 
                                      border-bottom: none; border-radius: 6px 6px 0 0; text-decoration: none; cursor: pointer; transition: all 0.2s;">
                                Stats
                            </a>
                        </li>
                        <li id="ad_stats_li" class="{{ ($activeTab ?? '') === 'ad-stats' ? 'active' : '' }}" style="margin-right: 4px;">
                            <a href="{{ route('call.script.ad-stats', ['uid' => $uid]) }}" 
                               style="display: inline-block; padding: 12px 20px; font-size: 14px; font-weight: 500; 
                                      color: {{ ($activeTab ?? '') === 'ad-stats' ? '#2563eb' : '#6b7280' }}; 
                                      background-color: {{ ($activeTab ?? '') === 'ad-stats' ? 'white' : '#f9fafb' }}; 
                                      border: 2px solid {{ ($activeTab ?? '') === 'ad-stats' ? '#2563eb' : 'transparent' }}; 
                                      border-bottom: none; border-radius: 6px 6px 0 0; text-decoration: none; cursor: pointer; transition: all 0.2s;">
                                Ad Stats
                            </a>
                        </li>
                        <li id="call_histry_li" style="margin-right: 4px;">
                            <a href="#calllog" style="display: inline-block; padding: 12px 20px; font-size: 14px; font-weight: 500; color: #6b7280; background-color: #f9fafb; border: 2px solid transparent; border-bottom: none; border-radius: 6px 6px 0 0; text-decoration: none; cursor: pointer; transition: all 0.2s;">
                                Call History
                            </a>
                        </li>
                    </ul>

                    <div id="scriptLoadingModal" class="loading-script-modal hidden">
                        <div class="loading-spinner">
                            <div class="spinner"></div>
                            <div>Loading messages...</div>
                        </div>
                    </div>
                    
                    <div class="tab-content" style="padding-top: 15px;">
                        <div class="tab-pane fade {{ ($activeTab ?? 'sinhala') === 'sinhala' ? 'active in' : '' }}" id="srl">
                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                    <div id="sheet-data-container" 
                                        style="max-width: 1100px; margin: 20px auto 0; font-family: sans-serif; display: list-item;">
                                        <!-- Content dynamically loaded by JavaScript -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="tab-pane fade" id="eng"></div>
                        <div class="tab-pane fade" id="tam"></div>
                        <div class="tab-pane fade {{ ($activeTab ?? '') === 'bundle-package' ? 'active in' : '' }}" id="bundles">
                            <div style="max-width: 1100px; margin: 20px auto; font-family: sans-serif; display: list-item;">
                                <div id="bundle-packages-container"></div>
                            </div>
                        </div>
                        <div class="tab-pane fade {{ ($activeTab ?? '') === 'stats' ? 'active in' : '' }}" id="stat">
                            <div style="max-width: 1100px; margin: 20px auto; font-family: sans-serif; display: list-item;">
                                <div id="stats-container"></div>
                            </div>
                        </div>
                        <div class="tab-pane fade {{ ($activeTab ?? '') === 'ad-stats' ? 'active in' : '' }}" id="ad-stats">
                            <div style="max-width: 1200px; margin: 20px auto; font-family: sans-serif;">
                                <div id="ad-stats-container">
                                    @if(!empty($statsData))
                                        <!-- Top Stats Row -->
                                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 24px;">
                                            <!-- Total Activities -->
                                            <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 12px; padding: 24px; color: white; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                                                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                                                    <i class="fa fa-clipboard-list" style="font-size: 24px;"></i>
                                                    <h3 style="font-size: 14px; font-weight: 500; margin: 0; opacity: 0.9;">Total Activities</h3>
                                                </div>
                                                <div style="font-size: 32px; font-weight: 700;">{{ $statsData['total_activities'] ?? 0 }}</div>
                                            </div>
                                            
                                            <!-- Recent Calls -->
                                            <div style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); border-radius: 12px; padding: 24px; color: white; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                                                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                                                    <i class="fa fa-phone" style="font-size: 24px;"></i>
                                                    <h3 style="font-size: 14px; font-weight: 500; margin: 0; opacity: 0.9;">Calls (Last 30 Days)</h3>
                                                </div>
                                                <div style="font-size: 32px; font-weight: 700;">{{ $statsData['recent_calls'] ?? 0 }}</div>
                                            </div>
                                            
                                            <!-- Average Score -->
                                            <div style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 12px; padding: 24px; color: white; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                                                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                                                    <i class="fa fa-star" style="font-size: 24px;"></i>
                                                    <h3 style="font-size: 14px; font-weight: 500; margin: 0; opacity: 0.9;">Average Lead Score</h3>
                                                </div>
                                                <div style="font-size: 32px; font-weight: 700;">{{ $statsData['avg_score'] ?? 'N/A' }}</div>
                                            </div>
                                        </div>

                                        <!-- API Stats Section -->
                                        @if(!empty($statsData['api_stats']))
                                        <div style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 24px;">
                                            <h3 style="font-size: 20px; font-weight: 600; color: #1f2937; margin-bottom: 16px; border-bottom: 2px solid #e5e7eb; padding-bottom: 12px;">
                                                <i class="fa fa-chart-bar" style="margin-right: 8px; color: #2563eb;"></i>
                                                Package & Ad Statistics
                                            </h3>
                                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px;">
                                                @foreach($statsData['api_stats'] as $stat)
                                                <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px;">
                                                    <div style="font-size: 12px; font-weight: 500; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">
                                                        {{ $stat['label'] ?? '' }}
                                                    </div>
                                                    <div style="font-size: 24px; font-weight: 700; color: #1f2937;">
                                                        {{ $stat['value'] ?? '' }}
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        @endif

                                        <!-- Activity Summary Section -->
                                        <div style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 24px;">
                                            <h3 style="font-size: 20px; font-weight: 600; color: #1f2937; margin-bottom: 16px; border-bottom: 2px solid #e5e7eb; padding-bottom: 12px;">
                                                <i class="fa fa-calendar-days" style="margin-right: 8px; color: #2563eb;"></i>
                                                Activity Summary
                                            </h3>
                                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                                                <div style="background: #f0fdf4; border: 1px solid #86efac; border-radius: 8px; padding: 16px; text-align: center;">
                                                    <div style="font-size: 12px; font-weight: 500; color: #15803d; margin-bottom: 8px;">
                                                        <i class="fa fa-clock" style="margin-right: 4px;"></i>Today
                                                    </div>
                                                    <div style="font-size: 28px; font-weight: 700; color: #15803d;">
                                                        {{ $statsData['today_activities'] ?? 0 }}
                                                    </div>
                                                </div>
                                                
                                                <div style="background: #eff6ff; border: 1px solid #93c5fd; border-radius: 8px; padding: 16px; text-align: center;">
                                                    <div style="font-size: 12px; font-weight: 500; color: #1e40af; margin-bottom: 8px;">
                                                        <i class="fa fa-calendar" style="margin-right: 4px;"></i>This Week
                                                    </div>
                                                    <div style="font-size: 28px; font-weight: 700; color: #1e40af;">
                                                        {{ $statsData['week_activities'] ?? 0 }}
                                                    </div>
                                                </div>
                                                
                                                <div style="background: #fffbeb; border: 1px solid #fde047; border-radius: 8px; padding: 16px; text-align: center;">
                                                    <div style="font-size: 12px; font-weight: 500; color: #a16207; margin-bottom: 8px;">
                                                        <i class="fa fa-calendar-days" style="margin-right: 4px;"></i>This Month
                                                    </div>
                                                    <div style="font-size: 28px; font-weight: 700; color: #a16207;">
                                                        {{ $statsData['month_activities'] ?? 0 }}
                                                    </div>
                                                </div>
                                                
                                                <div style="background: #f0f9ff; border: 1px solid #7dd3fc; border-radius: 8px; padding: 16px; text-align: center;">
                                                    <div style="font-size: 12px; font-weight: 500; color: #0369a1; margin-bottom: 8px;">
                                                        <i class="fa fa-chart-bar" style="margin-right: 4px;"></i>All Time
                                                    </div>
                                                    <div style="font-size: 28px; font-weight: 700; color: #0369a1;">
                                                        {{ $statsData['all_time_activities'] ?? 0 }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Activity Progress Section -->
                                        <div style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 24px;">
                                            <h3 style="font-size: 20px; font-weight: 600; color: #1f2937; margin-bottom: 16px; border-bottom: 2px solid #e5e7eb; padding-bottom: 12px;">
                                                <i class="fa fa-chart-bar-square" style="margin-right: 8px; color: #2563eb;"></i>
                                                Activity Progress
                                            </h3>
                                            <div style="background: #f0f9ff; border: 1px solid #7dd3fc; border-radius: 8px; padding: 16px;">
                                                <div style="font-size: 14px; font-weight: 500; color: #0369a1; margin-bottom: 8px;">Payment Status</div>
                                                <div style="display: inline-block; background: #0ea5e9; color: white; padding: 8px 16px; border-radius: 6px; font-weight: 600;">
                                                    {{ $statsData['payment_status'] ?? 'N/A' }}
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Activity Breakdown Section -->
                                        @if(!empty($statsData['activity_breakdown']))
                                        <div style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 24px;">
                                            <h3 style="font-size: 20px; font-weight: 600; color: #1f2937; margin-bottom: 16px; border-bottom: 2px solid #e5e7eb; padding-bottom: 12px;">
                                                <i class="fa fa-chart-pie" style="margin-right: 8px; color: #2563eb;"></i>
                                                Activity Breakdown
                                            </h3>
                                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px;">
                                                @foreach($statsData['activity_breakdown'] as $breakdown)
                                                <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px;">
                                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                                        <div style="font-size: 14px; font-weight: 600; color: #3b82f6;">
                                                            {{ $breakdown['type'] ?? 'Other' }}
                                                        </div>
                                                        <div style="background: #10b981; color: white; padding: 4px 12px; border-radius: 6px; font-weight: 700; font-size: 14px;">
                                                            {{ $breakdown['count'] ?? 0 }}
                                                        </div>
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        @endif
                                    @else
                                        <!-- Empty State -->
                                        {{-- <div style="background: #f9fafb; border-radius: 12px; padding: 48px; text-align: center;">
                                            <i class="fa fa-chart-pie" style="font-size: 48px; color: #9ca3af; margin-bottom: 16px;"></i>
                                            <p style="color: #6b7280; font-size: 14px;">No statistics data available</p>
                                        </div> --}}
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="calllog">
                             <div style="max-width: 1100px; margin: 20px auto; font-family: sans-serif; padding: 0 20px;">
                                 <ul class="timeline" id="call-log-data-container" style="list-style: none; padding: 0; margin: 0; position: relative;"></ul>
                             </div>
                        </div>

                    </div>
                </div>

                <div class="panel-footer">
                    <button type="button" id="scrpt-back-btn" class="btn btn-info" style="display: none;">Back</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script id="call-script-data" type="application/json">@json($script ?? [])</script>
<script id="property-data" type="application/json">@json($propertyData ?? [])</script>
<script id="active-tab-data" type="application/json">"{{ $activeTab ?? 'sinhala' }}"</script>
<script id="call-logs-data" type="application/json">@json($callLogs ?? [])</script>
<link rel="stylesheet" href="{{ asset('css/filament/filament/app.css') }}">
<script src="{{ asset('js/filament/schemas/components/callScript.js') }}"></script>
@endsection


