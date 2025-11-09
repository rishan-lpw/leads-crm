@extends('layouts.app')

@section('content')
<div class="container-fluid" style="margin: 0; padding: 0; width: 100%; max-width: 100vw; overflow-x: hidden;">
    <div class="row" style="margin: 0; width: 100%;">
        <div class="col-xs-12" style="padding: 0;">
            <div class="panel panel-default" style="margin: 0; border-radius: 0; border-left: none; border-right: none;">
                <div class="panel-heading" style="padding: 15px 20px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px;">
                        <div style="display: flex; align-items: center; gap: 10px; flex: 1; min-width: 200px;">
                            <h2 class="panel-title" id="scriptModalLabel" style="font-weight: bold; font-size: clamp(20px, 4vw, 32px); color: #ffffff; margin: 0; line-height: 1.2;">
                                Sales Call Transcript
                            </h2>
                        </div>
                        {{-- Take the mobile number from the customer table column 'mobile'. --}}
                        <button type="button" class="btn btn-success call-btn" data-phone="{{ request()->query('mobile') ?? request()->query('phone') ?? '' }}" style="background-color: #10b981; border-color: #10b981; color: white; padding: 10px 20px; border-radius: 6px; font-weight: 600; cursor: pointer; transition: all 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.1); white-space: nowrap; font-size: 14px;">
                            <i class="fa fa-phone"></i> <span class="call-btn-text">Call Customer</span>
                        </button>
                    </div>
                </div>

                <div class="panel-body" style="padding: 15px 20px;">

                    <ul class="nav nav-tabs" id="tabs" style="border-bottom: 2px solid #e5e7eb; margin-top: 10px; margin-bottom: 0; display: flex; list-style: none; padding-left: 0; overflow-x: auto; overflow-y: hidden; flex-wrap: nowrap; -webkit-overflow-scrolling: touch;">
                        <li class="{{ ($activeTab ?? 'sinhala') === 'sinhala' ? 'active' : '' }}" style="margin-right: 4px; flex-shrink: 0;">
                            <a href="{{ route('call.script.sinhala', ['uid' => $uid]) }}" 
                               style="display: inline-block; padding: 10px 16px; font-size: clamp(12px, 2vw, 14px); font-weight: 500; 
                                      color: {{ ($activeTab ?? 'sinhala') === 'sinhala' ? '#2563eb' : '#6b7280' }}; 
                                      background-color: {{ ($activeTab ?? 'sinhala') === 'sinhala' ? 'white' : '#f9fafb' }}; 
                                      border: 2px solid {{ ($activeTab ?? 'sinhala') === 'sinhala' ? '#2563eb' : 'transparent' }}; 
                                      border-bottom: none; border-radius: 6px 6px 0 0; text-decoration: none; cursor: pointer; transition: all 0.2s; white-space: nowrap;">
                                Sinhala
                            </a>
                        </li>
                        <li style="margin-right: 4px; flex-shrink: 0;">
                            <a href="#eng" style="display: inline-block; padding: 10px 16px; font-size: clamp(12px, 2vw, 14px); font-weight: 500; color: #6b7280; background-color: #f9fafb; border: 2px solid transparent; border-bottom: none; border-radius: 6px 6px 0 0; text-decoration: none; cursor: pointer; transition: all 0.2s; white-space: nowrap;">
                                English
                            </a>
                        </li>
                        <li style="margin-right: 4px; flex-shrink: 0;">
                            <a href="#tam" style="display: inline-block; padding: 10px 16px; font-size: clamp(12px, 2vw, 14px); font-weight: 500; color: #6b7280; background-color: #f9fafb; border: 2px solid transparent; border-bottom: none; border-radius: 6px 6px 0 0; text-decoration: none; cursor: pointer; transition: all 0.2s; white-space: nowrap;">
                                Tamil
                            </a>
                        </li>
                        <li id="bundle_packages_li" class="{{ ($activeTab ?? '') === 'bundle-package' ? 'active' : '' }}" style="margin-right: 4px; flex-shrink: 0;">
                            <a href="{{ route('call.script.bundle-package', ['uid' => $uid]) }}" 
                               style="display: inline-block; padding: 10px 16px; font-size: clamp(12px, 2vw, 14px); font-weight: 500; 
                                      color: {{ ($activeTab ?? '') === 'bundle-package' ? '#2563eb' : '#6b7280' }}; 
                                      background-color: {{ ($activeTab ?? '') === 'bundle-package' ? 'white' : '#f9fafb' }}; 
                                      border: 2px solid {{ ($activeTab ?? '') === 'bundle-package' ? '#2563eb' : 'transparent' }}; 
                                      border-bottom: none; border-radius: 6px 6px 0 0; text-decoration: none; cursor: pointer; transition: all 0.2s; white-space: nowrap;">
                                Bundle Packages
                            </a>
                        </li>
                        <li id="stats_li" class="{{ ($activeTab ?? '') === 'stats' ? 'active' : '' }}" style="margin-right: 4px; flex-shrink: 0;">
                            <a href="{{ route('call.script.stats', ['uid' => $uid]) }}" 
                               style="display: inline-block; padding: 10px 16px; font-size: clamp(12px, 2vw, 14px); font-weight: 500; 
                                      color: {{ ($activeTab ?? '') === 'stats' ? '#2563eb' : '#6b7280' }}; 
                                      background-color: {{ ($activeTab ?? '') === 'stats' ? 'white' : '#f9fafb' }}; 
                                      border: 2px solid {{ ($activeTab ?? '') === 'stats' ? '#2563eb' : 'transparent' }}; 
                                      border-bottom: none; border-radius: 6px 6px 0 0; text-decoration: none; cursor: pointer; transition: all 0.2s; white-space: nowrap;">
                                Stats
                            </a>
                        </li>
                        <li id="ad_stats_li" class="{{ ($activeTab ?? '') === 'ad-stats' ? 'active' : '' }}" style="margin-right: 4px; flex-shrink: 0;">
                            <a href="{{ route('call.script.ad-stats', ['uid' => $uid]) }}" 
                               style="display: inline-block; padding: 10px 16px; font-size: clamp(12px, 2vw, 14px); font-weight: 500; 
                                      color: {{ ($activeTab ?? '') === 'ad-stats' ? '#2563eb' : '#6b7280' }}; 
                                      background-color: {{ ($activeTab ?? '') === 'ad-stats' ? 'white' : '#f9fafb' }}; 
                                      border: 2px solid {{ ($activeTab ?? '') === 'ad-stats' ? '#2563eb' : 'transparent' }}; 
                                      border-bottom: none; border-radius: 6px 6px 0 0; text-decoration: none; cursor: pointer; transition: all 0.2s; white-space: nowrap;">
                                Ad Stats
                            </a>
                        </li>
                        <li id="call_histry_li" style="margin-right: 4px; flex-shrink: 0;">
                            <a href="#calllog" style="display: inline-block; padding: 10px 16px; font-size: clamp(12px, 2vw, 14px); font-weight: 500; color: #6b7280; background-color: #f9fafb; border: 2px solid transparent; border-bottom: none; border-radius: 6px 6px 0 0; text-decoration: none; cursor: pointer; transition: all 0.2s; white-space: nowrap;">
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
                            <div class="row" style="margin: 0;">
                                <div class="col-xs-12" style="padding: 0 10px;">
                                    <div id="sheet-data-container" 
                                        style="width: 100%; max-width: 100%; margin: 20px auto 0; font-family: sans-serif; display: list-item; padding: 0 10px; box-sizing: border-box;">
                                        <!-- Content dynamically loaded by JavaScript -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="tab-pane fade" id="eng"></div>
                        <div class="tab-pane fade" id="tam"></div>
                        <div class="tab-pane fade {{ ($activeTab ?? '') === 'bundle-package' ? 'active in' : '' }}" id="bundles">
                            <div style="width: 100%; max-width: 100%; margin: 20px auto; font-family: sans-serif; display: list-item; padding: 0 10px; box-sizing: border-box;">
                                <div id="bundle-packages-container"></div>
                            </div>
                        </div>
                        <div class="tab-pane fade {{ ($activeTab ?? '') === 'stats' ? 'active in' : '' }}" id="stat">
                            <div style="width: 100%; max-width: 100%; margin: 20px auto; font-family: sans-serif; display: list-item; padding: 0 10px; box-sizing: border-box;">
                                <div id="stats-container"></div>
                            </div>
                        </div>
                        <div class="tab-pane fade {{ ($activeTab ?? '') === 'ad-stats' ? 'active in' : '' }}" id="ad-stats">
                            <div style="width: 100%; max-width: 100%; margin: 20px auto; font-family: sans-serif; padding: 0 10px; box-sizing: border-box;">
                                <div id="ad-stats-container">
                                    @if(!empty($statsData['api_stats']) && is_array($statsData['api_stats']) && count($statsData['api_stats']) > 0)
                                        <!-- API Stats Section - Only data from getUserStatsForAdsNormalized() -->
                                        <div style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                            <h3 style="font-size: 20px; font-weight: 600; color: #1f2937; margin-bottom: 24px; border-bottom: 2px solid #e5e7eb; padding-bottom: 12px;">
                                                <i class="fa fa-chart-bar" style="margin-right: 8px; color: #2563eb;"></i>
                                                Package & Ad Statistics
                                            </h3>
                                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px;">
                                                @foreach($statsData['api_stats'] as $stat)
                                                <div style="background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%); border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; transition: all 0.2s; hover:shadow-md;">
                                                    <div style="font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                                                        <i class="fa fa-info-circle" style="font-size: 10px;"></i>
                                                        {{ $stat['label'] ?? 'N/A' }}
                                                    </div>
                                                    <div style="font-size: 28px; font-weight: 700; color: #1f2937; line-height: 1.2;">
                                                        {{ $stat['value'] ?? 'N/A' }}
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @else
                                        <!-- Empty State -->
                                        <div style="background: #f9fafb; border-radius: 12px; padding: 48px; text-align: center;">
                                            <i class="fa fa-chart-bar" style="font-size: 48px; color: #9ca3af; margin-bottom: 16px;"></i>
                                            <p style="color: #6b7280; font-size: 16px; font-weight: 500; margin: 0;">No ad statistics data available</p>
                                            <p style="color: #9ca3af; font-size: 14px; margin-top: 8px;">The statistics will appear here once data is available.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="calllog">
                             <div style="width: 100%; max-width: 100%; margin: 20px auto; font-family: sans-serif; padding: 0 10px; box-sizing: border-box;">
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


