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
                                        style="max-width: 1000px; margin: 20px auto 0; font-family: sans-serif; display: list-item;">
                                        <!-- Content dynamically loaded by JavaScript -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="tab-pane fade" id="eng"></div>
                        <div class="tab-pane fade" id="tam"></div>
                        <div class="tab-pane fade {{ ($activeTab ?? '') === 'bundle-package' ? 'active in' : '' }}" id="bundles">
                            <div style="max-width: 1000px; margin: 20px auto; font-family: sans-serif; display: list-item;">
                                <div id="bundle-packages-container"></div>
                            </div>
                        </div>
                        <div class="tab-pane fade {{ ($activeTab ?? '') === 'stats' ? 'active in' : '' }}" id="stat">
                            <div style="max-width: 1000px; margin: 20px auto; font-family: sans-serif; display: list-item;">
                                <div id="stats-container"></div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="calllog">
                             <div style="max-width: 1000px; margin: 20px auto; font-family: sans-serif; display: list-item;">
                                 <ul class="timeline" id="call-log-data-container"></ul>
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
<link rel="stylesheet" href="{{ asset('css/filament/filament/app.css') }}">
<script src="{{ asset('js/filament/schemas/components/callScript.js') }}"></script>
@endsection


