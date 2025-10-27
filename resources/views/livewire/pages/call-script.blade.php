@extends('layouts.app')

@section('content')
<div class="container-fluid" style="margin-top: 20px;">
    <div class="row">
        <div class="col-lg-10 col-lg-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <h2 class="panel-title" id="scriptModalLabel" style="font-weight: bold; margin: 0;">
                                Sales Call Transcript
                            </h2>
                        </div>
                        <button type="button" class="btn btn-success call-btn" data-phone="[Customer Phone Number]">
                            <i class="fa fa-phone"></i> Call Customer
                        </button>
                    </div>
                </div>

                <div class="panel-body">

                    <ul class="flex border-b border-gray-200" id="tabs">
                        <li class="mr-1 @if(true) -mb-px @endif"> {{-- Simulate 'active' logic --}}
                            <a class="inline-block py-2 px-4 text-sm font-medium border-l border-t border-r rounded-t @if(true) text-blue-600 bg-white border-blue-600 @else text-gray-500 hover:text-gray-600 hover:bg-gray-50 @endif" 
                               href="#srl">
                                Sinhala
                            </a>
                        </li>
                        <li class="mr-1">
                            <a class="inline-block py-2 px-4 text-sm font-medium border-l border-t border-r rounded-t text-gray-500 hover:text-gray-600 hover:bg-gray-50" 
                               href="#eng">
                                English
                            </a>
                        </li>
                        <li class="mr-1">
                            <a class="inline-block py-2 px-4 text-sm font-medium border-l border-t border-r rounded-t text-gray-500 hover:text-gray-600 hover:bg-gray-50" 
                               href="#tam">
                                Tamil
                            </a>
                        </li>
                        <li id="stats_li" class="mr-1">
                            <a class="inline-block py-2 px-4 text-sm font-medium border-l border-t border-r rounded-t text-gray-500 hover:text-gray-600 hover:bg-gray-50" 
                               href="#stat">
                                Stats
                            </a>
                        </li>
                        <li id="call_histry_li" class="mr-1">
                            <a class="inline-block py-2 px-4 text-sm font-medium border-l border-t border-r rounded-t text-gray-500 hover:text-gray-600 hover:bg-gray-50" 
                               href="#calllog">
                                Call History
                            </a>
                        </li>
                        <li id="bundle_packages_li" class="mr-1">
                            <a class="inline-block py-2 px-4 text-sm font-medium border-l border-t border-r rounded-t text-gray-500 hover:text-gray-600 hover:bg-gray-50" 
                               href="#bundle">
                                Bundle Packages
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
                        <div class="tab-pane fade active in" id="srl">
                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                    <div id="sheet-data-container" 
                                        style="max-width: 850px; margin: 20px auto 0; font-family: sans-serif; display: list-item;">

                                        <h4 style="margin-bottom: 5px; font-weight: bold;">Opening Greeting</h4>
                                        <hr style="margin-top: 5px; margin-bottom: 10px; border: 0; height: 1px; background-color: #eee;">
                                        
                                        <div class="message-card">
                                            <div class="message-speaker" style="color: #6f42c1;">
                                                Good
                                            </div>
                                            <div class="message-content">
                                                <p>
                                                    <span class="braced-text">Morning</span>.
                                                </p>
                                                <p>
                                                    මම Manoj කතාකරන්නේ. මේ වෙලාවේ කතා කරන්න පුලුවන්ද?
                                                </p>
                                                <p>
                                                    1. Sir/ Miss ගෙ Ad එකක් දාලා තිබ්බ <span class="bracketed-text">Miriswatta Land</span> එකක් <span class="braced-text">විකුනන්න</span>. ඒක සම්බන්දයෙන් කතා කරන්නෙ.
                                                </p>
                                            </div>
                                        </div>

                                        <h4 style="margin-bottom: 5px;">Rejection Options</h4>
                                        <hr style="margin-top: 5px; margin-bottom: 10px; border: 0; height: 1px; background-color: #eee;">
                                        
                                        <div class="rejection-accordion" style="margin-top: 12px; border-radius: 8px; overflow: hidden; background: #fff5f5;">
                                            <div class="rejection-header">
                                                Need More time/ Will try on next week
                                                <span>▼</span>
                                            </div>
                                            <div class="rejection-details"></div>
                                            
                                            <div class="rejection-header">
                                                I have to ask someone else
                                                <span>▼</span>
                                            </div>
                                            <div class="rejection-details"></div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="tab-pane fade" id="eng"></div>
                        <div class="tab-pane fade" id="tam"></div>
                        <div class="tab-pane fade" id="stat"></div>
                        <div class="tab-pane fade" id="calllog">
                             <div style="max-width: 850px; margin: 20px auto; font-family: sans-serif; display: list-item;">
                                 <ul class="timeline" id="call-log-data-container"></ul>
                             </div>
                        </div>
                        
                        <div class="tab-pane fade" id="bundle">
                             <div style="max-width: 850px; margin: 20px auto; font-family: sans-serif; display: list-item;">
                                 <div id="bundle-packages-container"></div>
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
<link rel="stylesheet" href="{{ asset('css/filament/filament/app.css') }}">
<script src="{{ asset('js/filament/schemas/components/callScript.js') }}"></script>
@endsection


